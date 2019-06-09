<?php


class Subscription_Payu_Latam_SPL_Admin
{
    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'subscription_payu_latam_spl_menu'));
        add_action( 'wp_ajax_subscription_payu_latam_spl',array($this,'subscription_payu_latam_spl_ajax'));

    }

    public function subscription_payu_latam_spl_menu()
    {
        add_submenu_page(
            null,
            '',
            '',
            'manage_options',
            'subscription-payu-latam-install-setp',
            array($this, 'subscription_payu_latam_install_step')
        );

        add_action( 'admin_footer', array( $this, 'enqueue_scripts_admin' ) );
    }

    public function subscription_payu_latam_install_step()
    {

        $dir = trailingslashit(WP_PLUGIN_DIR) . trailingslashit('woocommerce-subscriptions/woocommerce-subscriptions.php');

        if (!file_exists($dir) && !class_exists('WC_Subscriptions')){
            ?>
            <div class="wrap about-wrap">
                <h3><?php _e( 'Necesitamos activar las suscripciones en Woocommerce' ); ?></h3>
                <button class="button-primary subscription_payu_latam_spl_enable" type="button">Activar</button>
            </div>
            <?php
        }else{
            wp_redirect(admin_url( 'admin.php?page=wc-settings&tab=checkout&section=subscription_payu_latam'));
        }
    }

    public function enqueue_scripts_admin()
    {
        wp_enqueue_script('admin_js_subscription_payu_latam_spl', suscription_payu_latam_pls()->plugin_url."assets/js/sweetalert2.js", array('jquery'), suscription_payu_latam_pls()->version, true);
        wp_enqueue_script( 'subscription_payu_latam_spl', suscription_payu_latam_pls()->plugin_url . 'assets/js/config.js', array( 'jquery' ), suscription_payu_latam_pls()->version, true );
        wp_localize_script( 'subscription_payu_latam_spl', 'subscriptionPayuLatam', array(
            'urlConfig' => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=subscription_payu_latam')
        ) );
    }

    public function subscription_payu_latam_spl_ajax()
    {

        $pluginPath = trailingslashit(WP_PLUGIN_DIR);

        $fileDownload = $pluginPath . 'master.zip';

        $file = download_url('https://github.com/wp-premium/woocommerce-subscriptions/archive/master.zip');

        $fileCurrent =  $pluginPath . 'woocommerce-subscriptions-master';

        $fileNew = $pluginPath . 'woocommerce-subscriptions';

        $fileActive = trailingslashit($fileNew) . "woocommerce-subscriptions.php";

        $status = array('status' => false);

        if(is_wp_error( $file ))
            wp_die(wp_json_encode($status));

        rename($file, $fileDownload);

        $zip = new ZipArchive;
        if ($zip->open($fileDownload) === true) {
            $zip->extractTo(WP_PLUGIN_DIR);
            $zip->close();
        } else {
            unlink($fileDownload);
            wp_die(wp_json_encode($status));
        }

        rename($fileCurrent, $fileNew);
        unlink($fileDownload);
        activate_plugin( $fileActive );

        $status = array('status' => true);
        wp_die(wp_json_encode($status));
    }
}