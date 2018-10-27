<?php
  if( isset( $_POST['cln_rate'] ) && $_POST['cln_rate']){
    $descuento = $_POST['cln_rate'];
    update_option('cln_rate', $descuento);
  }else if( isset( $_POST['cln_user'] ) && $_POST['cln_token'] ){
    $user  = $_POST['cln_user'];
    $token = $_POST['cln_token'];
    update_option('cln_user', $user);
    update_option('cln_token', $token);
  }
?>

<h1>Configuraciones</h1>
<h3><span class="dashicons dashicons-chart-pie"></span>Descuento actual: <?= get_option('cln_rate'); ?>%</h3>

<form action="<?= admin_url() ?>admin.php?page=cln-admin-menu" method="POST" class="form-descuento">
  <table>
    <tbody>
      <tr>
        <th><label for="title">Porcentaje</label></th>
        <td><input type="text" name="cln_rate" id="cln_rate" class="regular-text"></td>
      </tr>
      <tr>
        <th></th>
        <td style="text-align: right;">
          <button type="submit" class="button" style="cursor:pointer;">Actualizar</button>
        </td>
      </tr>
    </tbody>
  </table>
</form>

<hr style="margin: 20px 0; padding: 0;">

<h3><span class="dashicons dashicons-admin-site"></span> Credenciales del WebService</h3>
<form action="<?= admin_url() ?>admin.php?page=cln-admin-menu" method="POST" class="form-descuento">
  <table>
    <tbody>
      <tr>
        <th><label for="title">Usuario</label></th>
        <td><input type="text" name="cln_user" class="regular-text" id="cln_user" value="<?= get_option("cln_user"); ?>"></td>
      </tr>
      <tr>
        <th><label for="title">Token</label></th>
        <td><input type="text" name="cln_token" class="regular-text" id="cln_token" value="<?= get_option("cln_token");  ?>"></td>
      </tr>
      <tr>
        <th></th>
        <td style="text-align: right;">
          <button class="button" style="cursor:pointer;">Actualizar</button>
        </td>
      </tr>
    </tbody>
  </table>
</form>
