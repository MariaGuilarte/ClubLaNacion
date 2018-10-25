<?php 
  if( isset( $_POST['cln_rate'] ) ){
    $descuento = $_POST['cln_rate'];
    update_option('cln_rate', $descuento);
  }
?>

<h1>Aplicar monto del descuento</h1>
<span>Descuento actual: <?= get_option('cln_rate'); ?>%</span>

<form action="<?= admin_url() ?>admin.php?page=cln-admin-menu" method="POST" class="form-descuento">
  <div class="form-group">
    <label for="title">Nuevo valor</label>
    <input type="text" name="cln_rate" id="cln_rate" placeholder="%">
  </div>
  <div class="form-group">
    <input type="submit" value="Actualizar" style="cursor:pointer;"></input>
  </div>
</form>

