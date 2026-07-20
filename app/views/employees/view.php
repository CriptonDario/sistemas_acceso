<div class="card" style="width: 18rem;">
  <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $empleado['codigo']; ?>" class="card-img-top" alt="QR Code">
  
  <div class="card-body">
    <h5 class="card-title"><?php echo $empleado['nombres']; ?></h5>
    <p class="card-text">Código: <?php echo $empleado['codigo']; ?></p>
    <a href="#" class="btn btn-primary" onclick="window.print()">Imprimir Carnet</a>
  </div>
</div>
