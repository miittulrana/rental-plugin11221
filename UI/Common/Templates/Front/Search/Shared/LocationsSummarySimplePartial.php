<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h2 class="summary-page-title"><?=esc_html($pageLabel);?></h2>
<?php
if($pickupDateVisible || $returnDateVisible || $pickupLocationVisible || $returnLocationVisible):
    print('<div class="location-summary">');

    if($pickupDateVisible || $returnDateVisible):
        print('<div class="summary-data-row">');
            print('<div class="summary-caption">');
                print(esc_html($lang['LANG_PERIOD_TEXT']));
                if($pickup['print_full_address'] == "" && $return['print_full_address'] == ""):
                    print( '  '.esc_html($expectedDurationText));
                endif;
                print(':');
            print('</div>');
            print('<div class="location-group">');
                print('<div class="summary-data">');
                    print('<div class="summary-data-icon"><i class="fa fa-calendar" aria-hidden="true"></i></div>');
                    print('<div class="summary-data-text">');
                        print(esc_html($objSearch->getI18nExpectedPickupDate()).' <strong>'.esc_html($objSearch->getI18nExpectedPickupTime()).'</strong>');
                        if($pickupInAfterHours):
                            print(' ('.esc_html($lang['LANG_ORDER_NIGHTLY_RATE_TEXT']).')');
                        endif;
                    print('</div>');
                print('</div>');
                print('<div class="summary-data">');
                    print('<div class="summary-data-icon"><i class="fa fa-calendar" aria-hidden="true"></i></div>');
                    print('<div class="summary-data-text">');
                        print(esc_html($objSearch->getI18nExpectedReturnDate()).' <strong>'.esc_html($objSearch->getI18nExpectedReturnTime()).'</strong>');
                        if($returnInAfterHours):
                            print(' ('.esc_html($lang['LANG_ORDER_NIGHTLY_RATE_TEXT']).')');
                        endif;
                    print('</div>');
                print('</div>');
                if($pickup['print_full_address'] != "" || $return['print_full_address'] != ""):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_DURATION_TEXT']).':</strong> '.esc_html($expectedDurationText));
                            print($pickup['two_lines_address'] == true || $return['two_lines_address'] == true ? '<br />&nbsp;' : '');
                        print('</div>');
                    print('</div>');
                endif;
                if($distance['print_distance'] != ""):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($showWorkingHours):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if(($pickup['lunch_enabled'] || $return['lunch_enabled']) && $showLocationSimpleFees):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
            print('</div>');
        print('</div>');
    endif;

    if($pickupLocationVisible && $objSearch->getPickupLocationId() > 0):
        print('<div class="summary-data-row">');
            print('<div class="summary-caption">'.esc_html($lang['LANG_ORDER_PICKUP_TEXT']).':</div>');
            print('<div class="location-group">');
                print('<div class="summary-data">');
                    print('<div class="summary-data-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></div>');
                    print('<div class="summary-data-text">'.$pickup['print_translated_location_name'].'</div>');
                print('</div>');
                if($pickup['print_full_address'] != ""):
                    // If pickup address is set
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-map-signs" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">'.$pickup['print_full_address'].'</div>');
                    print('</div>');
                elseif($return['print_full_address'] != ""):
                    // If there is a return address set, then add a blank line
                    print('<div class="summary-data">'.($return['two_lines_address'] == true ? '<br />&nbsp;' : '&nbsp;').'</div>');
                endif;
                if($distance['print_distance'] != ""):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($showWorkingHours):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_ORDER_BUSINESS_HOURS_TEXT']).':</strong> '.$pickup['print_open_hours']);
                        print('</div>');
                    print('</div>');
                elseif($showWorkingHours):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($pickup['lunch_enabled']):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-cutlery" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_LOCATION_LUNCH_TIME_TEXT']).':</strong> '.$pickup['print_lunch_hours']);
                        print('</div>');
                    print('</div>');
                elseif($return['lunch_enabled']):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($showLocationSimpleFees):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-money" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_ORDER_FEE_TEXT']).':</strong> ');
                            print('<span title="'.$pickup['print_current_pickup_fee_details'].'">');
                                print($pickup['unit_print'][$pickupInAfterHours ? 'afterhours_pickup_fee_dynamic' : 'pickup_fee_dynamic']);
                            print('</span>');
                        print('</div>');
                    print('</div>');
                elseif(($pickupDateVisible || $returnDateVisible) && !$pickup['lunch_enabled'] && !$return['lunch_enabled']):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
            print('</div>');
        print('</div>');
    endif;

    if($returnLocationVisible && $objSearch->getReturnLocationId() > 0):
        print('<div class="summary-data-row">');
            print('<div class="summary-caption">'.esc_html($lang['LANG_ORDER_RETURN_TEXT']).':</div>');
            print('<div class="location-group">');
                print('<div class="summary-data">');
                    print('<div class="summary-data-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></div>');
                    print('<div class="summary-data-text">'.$return['print_translated_location_name'].'</div>');
                print('</div>');
                if($return['print_full_address'] != ""):
                    // If pickup address is set
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-map-signs" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">'.$return['print_full_address'].'</div>');
                    print('</div>');
                elseif($pickup['print_full_address'] != ""):
                    // If there is a pickup address set, then add a blank line
                    print('<div class="summary-data">'.($pickup['two_lines_address'] == true ? '<br />&nbsp;' : '&nbsp;').'</div>');
                endif;
                if($distance['print_distance'] != ""):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-location-arrow" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            printf(esc_html($lang['LANG_DISTANCE_AWAY_TEXT']), $distance['print_distance']);
                        print('</div>');
                    print('</div>');
                endif;
                if($showWorkingHours):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_ORDER_BUSINESS_HOURS_TEXT']).':</strong> '.$return['print_open_hours']);
                        print('</div>');
                    print('</div>');
                elseif($showWorkingHours):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($return['lunch_enabled']):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-cutlery" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_LOCATION_LUNCH_TIME_TEXT']).':</strong> '.$return['print_lunch_hours']);
                        print('</div>');
                    print('</div>');
                elseif($pickup['lunch_enabled']):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
                if($showLocationSimpleFees):
                    print('<div class="summary-data">');
                        print('<div class="summary-data-icon"><i class="fa fa-money" aria-hidden="true"></i></div>');
                        print('<div class="summary-data-text">');
                            print('<strong>'.esc_html($lang['LANG_ORDER_FEE_TEXT']).':</strong> ');
                            print('<span title="'.$return['print_current_return_fee_details'].'">');
                                print($return['unit_print'][$returnInAfterHours ? 'afterhours_return_fee_dynamic' : 'return_fee_dynamic']);
                            print('</span>');
                        print('</div>');
                    print('</div>');
                elseif(($pickupDateVisible || $returnDateVisible) && !$pickup['lunch_enabled'] && !$return['lunch_enabled']):
                    print('<div class="summary-data">&nbsp;</div>');
                endif;
            print('</div>');
        print('</div>');
    endif;

    print('</div>');
endif;