<?php
/**
 * AsignacionDocente
 *
 * Lógica de niveles:
 *  - PRIMARIA / INICIAL → tipo_asignacion = 'aula'
 *    Un docente cubre TODO el grado. materia_id = NULL.
 *    Puede ingresar notas de cualquier materia de ese grado.
 *
 *  - SECUNDARIA → tipo_asignacion = 'curso'
 *    Un docente por materia específica. materia_id = ID de materia.
 *    Solo puede ingresar notas de esa materia.
 */
class AsignacionDocente {
    private $conn;
    private $tabla = "docente_materia_grado";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── Todas las asignaciones del año (para admin) ───────────────────────
    public function getAll($anio = null) {
        $anio = $anio ?? date('Y');
        $stmt = $this->conn->prepare(
            "SELECT d.*,
                    p.nombres, p.apellidos, p.codigo as cod_personal, p.cargo,
                    m.nombre as materia_nombre,
                    g.nombre as grado_nombre, g.nivel
             FROM {$this->tabla} d
             INNER JOIN personal p ON d.personal_id = p.id
             LEFT  JOIN materias m ON d.materia_id  = m.id
             INNER JOIN grados   g ON d.grado_id    = g.id
             WHERE d.anio = :anio
             ORDER BY g.nivel, g.id, d.tipo_asignacion, m.nombre, p.apellidos"
        );
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Asignaciones de un docente específico ─────────────────────────────
    public function getByDocente($personal_id, $anio = null) {
        $anio = $anio ?? date('Y');
        $stmt = $this->conn->prepare(
            "SELECT d.*,
                    m.nombre as materia_nombre,
                    g.nombre as grado_nombre, g.nivel
             FROM {$this->tabla} d
             LEFT  JOIN materias m ON d.materia_id = m.id
             INNER JOIN grados   g ON d.grado_id   = g.id
             WHERE d.personal_id = :pid AND d.anio = :anio AND d.activo = 1
             ORDER BY g.nivel, g.id, m.nombre"
        );
        $stmt->bindValue(':pid',  $personal_id, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio,        PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Crear asignación ─────────────────────────────────────────────────
    // Para primaria: materia_id = null, tipo = 'aula'
    // Para secundaria: materia_id = X, tipo = 'curso'
    public function create($personal_id, $materia_id, $grado_id, $anio, $tipo) {
        if (!in_array($tipo, ['aula','curso'])) $tipo = 'curso';

        // Para aula (primaria) materia_id es NULL
        $mat = ($tipo === 'aula') ? null : intval($materia_id);

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->tabla}
             (personal_id, materia_id, grado_id, anio, activo, tipo_asignacion)
             VALUES (:pid, :mid, :gid, :anio, 1, :tipo)"
        );
        $stmt->bindValue(':pid',  $personal_id, PDO::PARAM_INT);
        $stmt->bindValue(':mid',  $mat,         $mat === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':gid',  $grado_id,    PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio,        PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo);
        return $stmt->execute();
    }

    // ── Eliminar asignación ───────────────────────────────────────────────
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->tabla} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ── Verificar permiso para materia+grado ─────────────────────────────
    // Primaria: tiene una asignación de tipo 'aula' para ese grado
    // Secundaria: tiene asignación de tipo 'curso' para esa materia+grado
    public function tienePermiso($personal_id, $materia_id, $grado_id, $anio = null) {
        $anio = $anio ?? date('Y');

        // ¿Es docente de aula para este grado? (primaria/inicial)
        $stmtAula = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->tabla}
             WHERE personal_id=:pid AND grado_id=:gid
               AND anio=:anio AND activo=1 AND tipo_asignacion='aula'"
        );
        $stmtAula->execute([':pid'=>$personal_id,':gid'=>$grado_id,':anio'=>$anio]);
        if ($stmtAula->fetchColumn() > 0) return true;

        // ¿Tiene asignación de curso para esta materia específica?
        $stmtCurso = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->tabla}
             WHERE personal_id=:pid AND materia_id=:mid AND grado_id=:gid
               AND anio=:anio AND activo=1 AND tipo_asignacion='curso'"
        );
        $stmtCurso->execute([':pid'=>$personal_id,':mid'=>$materia_id,
                              ':gid'=>$grado_id,':anio'=>$anio]);
        return $stmtCurso->fetchColumn() > 0;
    }

    // ── Obtener tipo de docente para un grado ─────────────────────────────
    public function getTipoDocente($personal_id, $grado_id, $anio = null) {
        $anio = $anio ?? date('Y');
        $stmt = $this->conn->prepare(
            "SELECT tipo_asignacion FROM {$this->tabla}
             WHERE personal_id=:pid AND grado_id=:gid AND anio=:anio AND activo=1
             LIMIT 1"
        );
        $stmt->execute([':pid'=>$personal_id,':gid'=>$grado_id,':anio'=>$anio]);
        return $stmt->fetchColumn() ?: null;
    }
}
?>
