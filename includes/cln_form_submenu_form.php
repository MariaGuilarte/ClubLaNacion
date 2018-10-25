<?php do_action('cln_before_export_form'); ?>
<h1>Reportes del Club de la Nación</h1>
<div>
  <h3>Elige el rango de días</h3>
  <form action="<?= get_site_url() ?>/wp-admin/admin.php?page=cln-admin-submenu-1" method="POST">
    <label>Fecha inicial</label>
    <input type="text" name="from" class="date cln-csv-date-from" required readonly>

    <label>Fecha final</label>
    <input type="text" name="to" class="date cln-csv-date-to" required readonly>

    <input type="hidden" name="export_csv" value="true">
    <input type="submit" value="Exportar csv" style="cursor:pointer;" class="button">
  </form>
</div>
