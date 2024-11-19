<?php
/**
 * PayPal class alias to support old class name without the need to do changes to database
 * @note - This is a legacy class and it will be removed with FleetManagement v6.X release
 */
require_once 'PayPalToFleetManagementTranspiler.php';
if(!class_exists('NRSPayPal') && class_exists('PayPalToFleetManagementTranspiler'))
{
    class_alias('PayPalToFleetManagementTranspiler', 'NRSPayPal', true);
}