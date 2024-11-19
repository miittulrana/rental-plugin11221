<?php
/**
 * Stripe class alias to support old class name without the need to do changes to database
 * @note - This is a legacy class and it will be removed with FleetManagement v6.X release
 */
require_once 'StripeToFleetManagementTranspiler.php';
if(!class_exists('NRSStripe') && class_exists('StripeToFleetManagementTranspiler'))
{
    class_alias('StripeToFleetManagementTranspiler', 'NRSStripe', true);
}