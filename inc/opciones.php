<?php
// Crear opciones de datos de contacto
function page_ajustes(){
    add_menu_page('page','Page Ajustes','administrator','page_ajustes','page_ajustes_opciones','','20');

    //Llamar al registro de las opciones de nuestro tema

    add_action('admin_init','page_registrar_opciones');
    // add_submenu_page('page_ajustes','Datos de contacto','Datos de contacto','administrator','datos_de_contacto','datos_de_contacto');
  }

  add_action('admin_menu','page_ajustes');

function page_registrar_opciones(){
    //Registrar opciones una por campo
    register_setting('page_ajustes_opciones_grupo', 'numero_page');
    register_setting('page_ajustes_opciones_grupo', 'correo_page');
}

  function page_ajustes_opciones(){
      ?>
      <div class="wrap">
      <h1>Datos de contacto</h1>
        <form action="options.php" method="post">

        <?php settings_fields('page_ajustes_opciones_grupo');?>
        <?php do_settings_sections('page_ajustes_opciones_grupo');?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row" style="font-family:'Lato'; font-weight:400;">Número de contacto</th>
                    <td><input type="text" name="numero_page" value="<?php echo  esc_attr(get_option('numero_page'))?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row" style="font-family:'Lato'; font-weight:400;">Correo de contacto</th>
                    <td><input type="text" name="correo_page"  style="min-width:300px;"  value="<?php echo  esc_attr(get_option('correo_page'))?>"></td>
                </tr>

            </table>

            <?php submit_button();?>

        </form>
      </div>
    <?php
}


