<div class="cln" style="margin: 10px 0;">
  <label for="cln_code"><?php esc_html_e( 'CLN:', 'woocommerce' ); ?></label>
  <input type="number" name="cln_code" class="input-text" id="cln_code" placeholder="Ej: 2900386639130642" />
  <button type="submit" class="button" name="apply_cln" style="background: #264cad; color: #fff;">Desc. CLN</button>

  <?php $disabled = ! WC()->session->get("is_cln_member") ? "disabled" : ""; ?>

  <button type="submit" class="button" name="remove_cln" style="background: #c0392b; color: #fff;" <?= $disabled  ?>>Eliminar CLN</button>
</div>
