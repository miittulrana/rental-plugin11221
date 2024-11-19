<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$wpPostsAI = !isset($wpPostsAI) ? 0 : $wpPostsAI; // Define WordPress Auto Increment id if not set
$extAI = !isset($extAI) ? 0 : $extAI; // Define Extension Auto Increment id if not set
$arrReplaceSQL = !isset($arrReplaceSQL) ? array() : $arrReplaceSQL;
$arrPluginReplaceSQL = !isset($arrPluginReplaceSQL) ? array() : $arrPluginReplaceSQL;

// This table requires $wpPostsAI. Make sure than all " has a backslash - \" - Replace mode helps us here to avoid conflicts with already existing regular WordPress posts
$arrReplaceSQL['posts'] = '(`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
('.($wpPostsAI+1).', 1, \'2015-04-29 09:41:45\', \'2015-04-29 11:41:45\', \'[car_rental_system display="car-model" car_model="'.($extAI+1).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+1).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Peugeot 207\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'peugeot-207\', \'\', \'\', \'2015-04-29 09:41:45\', \'2015-04-29 11:41:45\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+1).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+2).', 1, \'2015-04-29 09:43:27\', \'2015-04-29 11:43:27\', \'[car_rental_system display="car-model" car_model="'.($extAI+2).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+2).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Suzuki Alto\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'suzuki-alto\', \'\', \'\', \'2015-05-01 17:13:49\', \'2015-05-01 19:13:49\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+2).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+3).', 1, \'2015-04-29 09:44:51\', \'2015-04-29 11:44:51\', \'[car_rental_system display="car-model" car_model="'.($extAI+3).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+3).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Opel Vivaro\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'opel-vivaro\', \'\', \'\', \'2015-04-29 09:44:51\', \'2015-04-29 11:44:51\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+3).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+4).', 1, \'2015-04-29 09:46:11\', \'2015-04-29 11:46:11\', \'[car_rental_system display="car-model" car_model="'.($extAI+4).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+4).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Peugeot Boxer\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'peugeot-boxer\', \'\', \'\', \'2015-04-29 09:46:12\', \'2015-04-29 11:46:12\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+4).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+5).', 1, \'2015-04-29 09:47:55\', \'2015-04-29 11:47:55\', \'[car_rental_system display="car-model" car_model="'.($extAI+5).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+5).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Audi A6\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'audi-a6\', \'\', \'\', \'2015-04-29 09:47:56\', \'2015-04-29 11:47:56\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+5).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+6).', 1, \'2015-04-29 09:49:01\', \'2015-04-29 11:49:01\', \'[car_rental_system display="car-model" car_model="'.($extAI+6).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+6).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Citroen C5\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'citroen-c5\', \'\', \'\', \'2015-04-29 09:49:01\', \'2015-04-29 11:49:01\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+6).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+8).', 1, \'2015-04-29 09:51:20\', \'2015-04-29 11:51:20\', \'[car_rental_system display="car-model" car_model="'.($extAI+8).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+8).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Opel Astra Sport Tourer\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'opel-astra-sport-tourer\', \'\', \'\', \'2015-04-29 09:51:21\', \'2015-04-29 11:51:21\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+8).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+9).', 1, \'2015-04-29 09:52:23\', \'2015-04-29 11:52:23\', \'[car_rental_system display="car-model" car_model="'.($extAI+9).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+9).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Opel Insignia\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'opel-insignia\', \'\', \'\', \'2015-04-29 09:52:24\', \'2015-04-29 11:52:24\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+9).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+10).', 1, \'2015-04-29 09:53:32\', \'2015-04-29 11:53:32\', \'[car_rental_system display="car-model" car_model="'.($extAI+10).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+10).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Mazda 6\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'mazda-6\', \'\', \'\', \'2015-04-29 09:53:32\', \'2015-04-29 11:53:32\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+10).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+12).', 1, \'2015-04-29 09:55:49\', \'2015-04-29 11:55:49\', \'[car_rental_system display="car-model" car_model="'.($extAI+12).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+12).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Mercedes ML350\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'mercedes-ml350\', \'\', \'\', \'2015-04-29 09:55:49\', \'2015-04-29 11:55:49\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+12).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+13).', 1, \'2015-04-29 09:57:01\', \'2015-04-29 11:57:01\', \'[car_rental_system display="car-model" car_model="'.($extAI+13).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+13).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Nissan Qashqai\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'nissan-qashqai\', \'\', \'\', \'2015-04-29 09:57:01\', \'2015-04-29 11:57:01\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+13).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+14).', 1, \'2015-09-09 11:55:55\', \'2015-09-09 18:55:55\', \'[car_rental_system display="car-model" car_model="'.($extAI+14).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+14).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Ford Fiesta\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'ford-fiesta\', \'\', \'\', \'2015-09-09 11:55:55\', \'2015-09-09 18:55:55\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+14).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+15).', 1, \'2015-09-08 19:49:42\', \'2015-09-09 02:49:42\', \'[car_rental_system display="car-model" car_model="'.($extAI+15).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+15).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Nissan Qashqai+2\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'nissan-qashqai2\', \'\', \'\', \'2015-09-08 19:49:42\', \'2015-09-09 02:49:42\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+15).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+16).', 1, \'2015-09-08 19:37:57\', \'2015-09-09 02:37:57\', \'[car_rental_system display="car-model" car_model="'.($extAI+16).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+16).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Kia Ceed\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'kia-ceed\', \'\', \'\', \'2015-09-08 19:37:57\', \'2015-09-09 02:37:57\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+16).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+17).', 1, \'2015-09-08 19:43:14\', \'2015-09-09 02:43:14\', \'[car_rental_system display="car-model" car_model="'.($extAI+17).'" layout="details"]\r\n[car_rental_system display="search" car_model="'.($extAI+17).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'VW Touareg\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'vwtouareg\', \'\', \'\', \'2015-09-08 19:46:22\', \'2015-09-09 02:46:22\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_item&#038;p='.($wpPostsAI+17).'\', 0, \'car_rental_item\', \'\', 0),
('.($wpPostsAI+21).', 1, \'2017-03-18 09:59:46\', \'2017-03-18 16:59:46\', \'[car_rental_system display="location" location="'.($extAI+1).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+1).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'San Jose Intl. Airport (SJC)\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'san-jose-intl-airport-sjc\', \'\', \'\', \'2017-03-18 18:22:37\', \'2017-03-19 01:22:37\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+21).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+22).', 1, \'2017-03-18 09:59:27\', \'2017-03-18 16:59:27\', \'[car_rental_system display="location" location="'.($extAI+2).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+2).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Oakland Intl. Airport (OAK)\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'oakland-intl-airport-oak\', \'\', \'\', \'2017-03-18 18:23:09\', \'2017-03-19 01:23:09\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+22).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+23).', 1, \'2017-03-18 09:59:08\', \'2017-03-18 16:59:08\', \'[car_rental_system display="location" location="'.($extAI+3).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+3).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'San Francisco Intl. Airport (SFO)\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'san-francisco-intl-airport-sfo\', \'\', \'\', \'2017-03-18 18:24:01\', \'2017-03-19 01:24:01\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+23).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+24).', 1, \'2017-03-17 17:32:32\', \'2017-03-18 00:32:32\', \'[car_rental_system display="location" location="'.($extAI+4).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+4).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Native Rental HQ\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'native-rental-hq\', \'\', \'\', \'2017-03-18 18:22:13\', \'2017-03-19 01:22:13\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+24).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+25).', 1, \'2017-03-18 10:00:02\', \'2017-03-18 17:00:02\', \'[car_rental_system display="location" location="'.($extAI+5).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+5).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Los Angeles Intl. Airport (LAX)\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'los-angeles-intl-airport-lax\', \'\', \'\', \'2017-03-18 10:00:02\', \'2017-03-18 17:00:02\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+25).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+26).', 1, \'2017-03-18 14:44:14\', \'2017-03-18 21:44:14\', \'[car_rental_system display="location" location="'.($extAI+6).'" layout="details"]\r\n[car_rental_system display="search" location="'.($extAI+6).'" action_page="'.($wpPostsAI+34).'" layouts="form,list,list,list,table,details,details,details,details"]\', \'Your preferred address\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'your-preferred-address\', \'\', \'\', \'2017-03-18 14:44:14\', \'2017-03-18 21:44:14\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_location&#038;p='.($wpPostsAI+26).'\', 0, \'car_rental_location\', \'\', 0),
('.($wpPostsAI+31).', 1, \'2015-10-27 14:18:30\', \'2015-10-27 21:18:30\', \'Payment was cancelled. Your reservation were not confirmed.\', \'Payment cancelled\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'payment-cancelled\', \'\', \'\', \'2015-10-27 14:18:30\', \'2015-10-27 21:18:30\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_page&#038;p='.($wpPostsAI+31).'\', 0, \'car_rental_page\', \'\', 0),
('.($wpPostsAI+32).', 1, \'2015-10-27 14:18:30\', \'2015-10-27 21:18:30\', \'Thank you. We received your payment. Your reservation is now confirmed.\', \'Reservation confirmed\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'booking-confirmed\', \'\', \'\', \'2015-10-27 14:18:30\', \'2015-10-27 21:18:30\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_page&#038;p='.($wpPostsAI+32).'\', 0, \'car_rental_page\', \'\', 0),
('.($wpPostsAI+33).', 1, \'2015-03-10 07:49:22\', \'2015-10-27 21:18:30\', \'<strong>PRICE</strong>\r\nPrice includes: Civilian insurance, KASKO insurance, Emergency road assistance 24/7, Sales Tax.\r\n\r\n<strong>PERIOD</strong>\r\nMinimal rental period 1 day.\r\n\r\n<strong>RENTER\'\'S LIABILITY</strong>\r\nThe renter is obligated to provide a vehicle to the lessee for a specified price.\r\nThe lessee becomes the righteous owner of a car during the period of rent. The renter insures the car and the lessee. The renter is obligated to check the condition of the vehicle and ensure that it is safe to use before the rent. The car that the renter provides must be clean and tanked up.\r\n\r\n<strong>LIMITATIONS</strong>\r\nThe lessee must have at least one year of driving experience,\r\nuse the car only in the territory of Lithuania (unless the renter specifies differently). The mileage of the rented car is unlimited.\r\n\r\n<strong>LESSEE\\\'S LIABILITY</strong>\r\nThe lessee must provide a valid driver license, which allows him to drive the rented car. The lessee must also submit a passport or ID card to the renter. The lessee must pay full price for the rent before the beginning of the rent.\r\nThe lessee must leave a deposit for the car which varies from 100 to 400 USD depending on the class of car. That may be used to cover any damages made to the car, that may occur during the period of car rent. When returning the car the lessee must also pay for the consumed fuel in the fuel tank, since the renter always provides a car with the full fuel tank.\r\n\r\n<strong>LIABILITY</strong>\r\n\r\nThe lessee undertakes full responsibility if a non insurable event occurs during the period of rent. The lessee takes the responsibility for all the consequences that may occur if transferring the car to a third person. If the lessee violates the road traffic rules he must pay all related penalties.\r\nIn case of car theft the lessee must compensate the damage if don\\\'t return the car keys and registration documents to the renter.\', \'Car rental terms and conditions\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'car-rental-terms-and-conditions\', \'\', \'\', \'2015-10-27 14:18:30\', \'2015-10-27 21:18:30\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_page&#038;p='.($wpPostsAI+33).'\', 0, \'car_rental_page\', \'\', 0),
('.($wpPostsAI+34).', 1, \'2017-03-23 08:38:23\', \'2017-03-23 15:38:23\', \'[car_rental_system display="search" layouts="form,list,list,list,table,details,details,details,details"]\', \'\', \'\', \'publish\', \'closed\', \'closed\', \'\', \'search\', \'\', \'\', \'2017-03-23 08:44:12\', \'2017-03-23 15:44:12\', \'\', 0, \'https://nativerental.com/cars/?post_type=car_rental_page&#038;p='.($wpPostsAI+34).'\', 0, \'car_rental_page\', \'\', 0)';

// This table requires $extAI
$arrPluginReplaceSQL['body_types'] = "(`body_type_id`, `body_type_title`, `body_type_order`, `blog_id`) VALUES
(".($extAI+1).", 'Intermediate', 2, '[BLOG_ID]'),
(".($extAI+2).", 'Compact', 1, '[BLOG_ID]'),
(".($extAI+3).", 'Station Wagon', 3, '[BLOG_ID]'),
(".($extAI+4).", 'SUV', 4, '[BLOG_ID]'),
(".($extAI+5).", 'Passenger Van', 5, '[BLOG_ID]')";

$arrPluginReplaceSQL['customers'] = "(`title`, `first_name`, `last_name`, `birthdate`, `street_address`, `city`, `state`, `zip_code`, `country`, `phone`, `email`, `comments`, `ip`, `existing_customer`, `registration_timestamp`, `last_visit_timestamp`, `blog_id`) VALUES
('Mr.', 'John', 'Smith', '1980-06-09', '625 2nd Street', 'San Francisco', 'CA', '94107', 'United States', '+14506004790', 'john.smith@gmail.com', 'Please leave car keys by the front door upon delivery.', '0.0.0.0', 0, 1444584654, 0, '[BLOG_ID]')";

$arrPluginReplaceSQL['discounts'] = "(`discount_type`, `price_plan_id`, `extra_id`, `period_from`, `period_till`, `discount_percentage`, `blog_id`) VALUES
(1, ".($extAI+19).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 86400, 259199, 0.000, '[BLOG_ID]'),
(1, ".($extAI+19).", 0, 259200, 518399, 14.610, '[BLOG_ID]'),
(1, ".($extAI+19).", 0, 518400, 777599, 29.250, '[BLOG_ID]'),
(1, ".($extAI+19).", 0, 777600, 1295999, 43.890, '[BLOG_ID]'),
(1, ".($extAI+19).", 0, 1296000, 2591999, 46.340, '[BLOG_ID]'),
(1, ".($extAI+19).", 0, 2592000, 31622399, 51.210, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 259200, 518399, 10.350, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 518400, 777599, 20.690, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 777600, 1295999, 31.040, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 1296000, 2591999, 41.390, '[BLOG_ID]'),
(1, ".($extAI+1).", 0, 2592000, 31622399, 51.730, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 259200, 518399, 9.080, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 518400, 777599, 13.640, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 777600, 1295999, 27.280, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 1296000, 2591999, 36.360, '[BLOG_ID]'),
(1, ".($extAI+3).", 0, 2592000, 31622399, 54.570, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 259200, 518399, 9.080, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 518400, 777599, 13.640, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 777600, 1295999, 27.280, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 1296000, 2591999, 36.360, '[BLOG_ID]'),
(1, ".($extAI+27).", 0, 2592000, 31622399, 54.570, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 259200, 518399, 7.320, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 518400, 777599, 14.610, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 777600, 1295999, 21.930, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 1296000, 2591999, 29.250, '[BLOG_ID]'),
(1, ".($extAI+17).", 0, 2592000, 31622399, 51.210, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 259200, 518399, 11.770, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 518400, 777599, 23.530, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 777600, 1295999, 35.300, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 1296000, 2591999, 37.250, '[BLOG_ID]'),
(1, ".($extAI+9).", 0, 2592000, 31622399, 41.190, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 259200, 518399, 7.320, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 518400, 777599, 14.610, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 777600, 1295999, 21.930, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 1296000, 2591999, 24.380, '[BLOG_ID]'),
(1, ".($extAI+11).", 0, 2592000, 31622399, 51.210, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 259200, 518399, 9.380, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 518400, 777599, 18.750, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 777600, 1295999, 28.130, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 1296000, 2591999, 37.500, '[BLOG_ID]'),
(1, ".($extAI+15).", 0, 2592000, 31622399, 46.880, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 259200, 518399, 9.380, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 518400, 777599, 18.750, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 777600, 1295999, 28.130, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 1296000, 2591999, 31.270, '[BLOG_ID]'),
(1, ".($extAI+31).", 0, 2592000, 31622399, 46.880, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 259200, 518399, 14.610, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 518400, 777599, 29.250, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 777600, 1295999, 43.890, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 1296000, 2591999, 46.340, '[BLOG_ID]'),
(1, ".($extAI+29).", 0, 2592000, 31622399, 51.210, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 259200, 518399, 14.610, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 518400, 777599, 29.250, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 777600, 1295999, 43.890, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 1296000, 2591999, 46.340, '[BLOG_ID]'),
(1, ".($extAI+25).", 0, 2592000, 31622399, 51.210, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 259200, 518399, 7.320, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 518400, 777599, 14.640, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 777600, 1295999, 21.930, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 1296000, 2591999, 29.250, '[BLOG_ID]'),
(1, ".($extAI+33).", 0, 2592000, 31622399, 39.020, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 259200, 518399, 10.720, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 518400, 777599, 21.430, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 777600, 1295999, 32.150, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 1296000, 2591999, 33.920, '[BLOG_ID]'),
(1, ".($extAI+23).", 0, 2592000, 31622399, 37.490, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 259200, 518399, 3.050, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 518400, 777599, 5.000, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 777600, 1295999, 6.670, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 1296000, 2591999, 8.350, '[BLOG_ID]'),
(1, ".($extAI+5).", 0, 2592000, 31622399, 10.000, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 259200, 518399, 1.760, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 518400, 777599, 3.020, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 777600, 1295999, 5.260, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 1296000, 2591999, 5.260, '[BLOG_ID]'),
(1, ".($extAI+7).", 0, 2592000, 31622399, 5.260, '[BLOG_ID]')";

$arrPluginReplaceSQL['distances'] = "(`pickup_location_id`, `return_location_id`, `show_distance`, `distance`, `distance_fee`, `blog_id`) VALUES
(".($extAI+4).", ".($extAI+3).", 1, 20.0, 35.00, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+4).", 1, 20.0, 20.00, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+2).", 1, 25.0, 35.00, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+4).", 1, 25.0, 20.00, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+1).", 1, 60.0, 35.00, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+4).", 1, 60.0, 20.00, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+5).", 1, 400.0, 100.00, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+4).", 1, 400.0, 85.00, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+4).", 0, 20.0, 10.00, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+2).", 1, 30.0, 35.00, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+3).", 1, 30.0, 35.00, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+1).", 1, 50.0, 35.00, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+3).", 1, 50.0, 35.00, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+5).", 1, 390.0, 100.00, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+3).", 1, 390.0, 100.00, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+3).", 0, 20.0, 10.00, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+1).", 1, 70.0, 35.00, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+2).", 1, 70.0, 35.00, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+5).", 1, 410.0, 100.00, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+2).", 1, 410.0, 100.00, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+2).", 0, 20.0, 10.00, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+5).", 1, 340.0, 100.00, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+1).", 1, 340.0, 100.00, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+1).", 0, 20.0, 10.00, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+5).", 0, 20.0, 10.00, '[BLOG_ID]')";

$arrPluginReplaceSQL['emails'] = "(`email_type`, `email_subject`, `email_body`, `blog_id`) VALUES
(1, 'Reservation details - no. [RESERVATION_CODE]', 'Dear, [CUSTOMER_NAME],\n\nYour reservation details were received.\n\nYour reservation details:\n\n[INVOICE]\n\nRegards,\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]', '[BLOG_ID]'),
(2, 'Reservation no. [RESERVATION_CODE] - confirmed', 'Dear, [CUSTOMER_NAME],\n\nWe received your payment. Your reservation is now confirmed.\n\nYour reservation details:\n\n[INVOICE]\n\nRegards,\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]', '[BLOG_ID]'),
(3, 'Reservation no. [RESERVATION_CODE] - cancelled', 'Dear, [CUSTOMER_NAME],\n\nYour reservation no. [RESERVATION_CODE] were cancelled.\n\nRegards,\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]', '[BLOG_ID]'),
(4, 'Notification: new reservation - [RESERVATION_CODE]', 'New reservation no. [RESERVATION_CODE] received from [CUSTOMER_NAME].\n\nReservation details:\n[INVOICE]', '[BLOG_ID]'),
(5, 'Notification: reservation paid - [RESERVATION_CODE]', 'Reservation no. [RESERVATION_CODE] was recently paid by [CUSTOMER_NAME].\n\nReservation details:\n[INVOICE]', '[BLOG_ID]'),
(6, 'Notification: reservation cancelled - [RESERVATION_CODE]', 'Reservation no. [RESERVATION_CODE] for [CUSTOMER_NAME] was recently cancelled.\n\nDetails of reservation, which were cancelled:\n[INVOICE]', '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['extras'] = "(`extra_id`, `extra_sku`, `partner_id`, `item_id`, `extra_name`, `price`, `price_type`, `fixed_rental_deposit`, `units_in_stock`, `max_units_per_booking`, `options_display_mode`, `options_measurement_unit`, `blog_id`) VALUES
(".($extAI+1).", 'EX_1', 0, 0, 'GPS', 3.31, 1, 0.00, 50, 1, 1, '', '[BLOG_ID]'),
(".($extAI+2).", 'EX_2', 0, 0, 'Baby Seat', 3.31, 1, 0.00, 50, 2, 1, '', '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['features'] = "(`feature_id`, `feature_title`, `display_in_item_list`, `blog_id`) VALUES
(".($extAI+1).", 'Power Steering', 1, '[BLOG_ID]'),
(".($extAI+2).", 'HD Audio System', 1, '[BLOG_ID]'),
(".($extAI+3).", 'Air Bags', 1, '[BLOG_ID]'),
(".($extAI+4).", 'A/C', 1, '[BLOG_ID]'),
(".($extAI+5).", 'Cruise Control', 1, '[BLOG_ID]'),
(".($extAI+6).", 'Electric Windows', 1, '[BLOG_ID]'),
(".($extAI+7).", 'Central Locking', 1, '[BLOG_ID]'),
(".($extAI+8).", 'ABS', 1, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['fuel_types'] = "(`fuel_type_id`, `fuel_type_title`, `blog_id`) VALUES
(".($extAI+1).", 'Petrol', '[BLOG_ID]'),
(".($extAI+2).", 'Diesel', '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['invoices'] = '(`booking_id`, `customer_name`, `customer_email`, `grand_total`, `fixed_deposit_amount`, `total_pay_now`, `total_pay_later`, `pickup_location`, `return_location`, `invoice`, `blog_id`) VALUES
('.($extAI+0).', \'Mr. John Smith\', \'john.smith@gmail.com\', \'$ 189.24\', \'$ 230.00\', \'$ 189.24\', \'$ 0.00\', \'2. San Francisco Intl. Airport (SFO) - Hwy 101, San Francisco, CA 94128\', \'3. Oakland Intl. Airport (OAK) - 1 Airport Dr, Oakland, CA 94621\',
\'<table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background-color:#eeeeee;width:840px;border:none" cellpadding="5" cellspacing="1">
    <tbody>
    <tr>
        <td align="left" style="font-weight:bold;background-color:#eeeeee;padding-left:5px" colspan="2">Customer Details</td>
    </tr>
    <tr>
        <td align="left" style="width:160px;background-color:#ffffff;padding-left:5px">Reservation code</td>
        <td align="left" style="background-color:#ffffff;padding-left:5px">R1AHKECN</td>
    </tr>
            <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Customer</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Mr. John Smith</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Date of Birth</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">1980-06-09</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Address</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">625 2nd Street</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">City</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">San Francisco</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">State</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">CA</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Postal Code</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">94107</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Country</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">United States</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Phone</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">+14506004790</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Email</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">john.smith@gmail.com</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Additional Comments</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Please leave car keys by the front door upon delivery.</td>
        </tr>
        </tbody>
</table>
<br />
<table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background:#999999;width:840px;border:none" cellpadding="5" cellspacing="1">
<tbody>
<tr>
    <td align="left" style="font-weight:bold;background-color:#eeeeee;padding-left:5px" colspan="3">Rental Details</td>
</tr>
            <tr style="background-color:#343434;color: white" class="location-headers">
            <td align="left" class="col1" style="padding-left:5px"><strong>Pick-up Location</strong></td>
            <td align="left" class="col2" style="padding-left:5px" colspan="2"><strong>Return Location</strong></td>
        </tr>

    <tr style="background-color:#FFFFFF" class="location-details">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1">
		2. San Francisco Intl. Airport (SFO) - Hwy 101, San Francisco, CA 94128            </td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="2">
		3. Oakland Intl. Airport (OAK) - 1 Airport Dr, Oakland, CA 94621            </td>
            </tr>


    <tr style="background-color:#343434;color: white" class="duration-headers">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1"><strong>Pick-up Date &amp; Time</strong></td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="1"><strong>Return Date &amp; Time</strong></td>
            <td align="right" class="col3" style="padding-right:5px"><strong>Period</strong></td>
            </tr>

    <tr style="background-color:#FFFFFF" class="duration-details">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1">
		October 16, 2015 &nbsp;&nbsp; 11:00 AM            </td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="1">
		October 19, 2015 &nbsp;&nbsp; 10:00 PM            </td>
            <td align="right" class="col3" style="padding-right:5px">
		4 days            </td>
            </tr>

<!-- ITEMS -->
    <tr class="item-models-header" style="background-color:#343434;color: white">
        <td align="left" class="col1" style="padding-left:5px"><strong>Selected Cars</strong></td>
        <td align="left" class="col2" style="padding-left:5px"><strong>Price</strong></td>
        <td align="right" class="col3" style="padding-right:5px"><strong>Total</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="items">
        <td align="left" class="col1" style="padding-left:5px">
		Mazda 6, Intermediate        </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 115.72 w/o Tax + $ 10.41 Tax = 1 x $ 126.14" style="cursor:pointer">
		$ 115.72            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 115.72 w/o Tax + $ 10.41 Tax = $ 126.14" style="cursor:pointer">
		$ 115.72            </span>
        </td>
    </tr>

<!-- PICKUP FEES -->
    <tr style="background-color:#343434;color: white" class="location-fees-header">
        <td align="left" class="col1" style="padding-left:5px" colspan="3"><strong>Location Fees</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px">Pick-up fee                    </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 7.44 w/o Tax + $ 0.67 Tax = 1 x $ 8.11" style="cursor:pointer">
		$ 7.44            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 7.44 w/o Tax + $ 0.67 Tax = $ 8.11" style="cursor:pointer">
		$ 7.44            </span>
        </td>
    </tr>



<!-- RETURN FEES -->
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px">Return fee           (Nightly rate applied)        </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 23.97 w/o Tax + $ 2.16 Tax = 1 x $ 26.13" style="cursor:pointer">
		$ 23.97            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 23.97 w/o Tax + $ 2.16 Tax = $ 26.13" style="cursor:pointer">
		$ 23.97            </span>
        </td>
    </tr>

<!-- EXTRAS -->
    <tr class="extras-header" style="background-color:#343434;color: white">
       <td align="left" class="col1" colspan="3"><strong>Rental Options</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="extras">
         <td align="left" class="col1" style="padding-left:5px">Baby Seat</td>
         <td align="left" class="col2" style="padding-left:5px">
             <span title="1 extra(s) x $ 13.24 w/o Tax + $ 1.19 Tax = 1 x $ 14.43" style="cursor:pointer">
		$ 13.24             </span>
         </td>
         <td align="right" class="col3" style="padding-right:5px">
             <span title="$ 13.24 w/o Tax + $ 1.19 Tax = $ 14.43" style="cursor:pointer">
		$ 13.24            </span>
         </td>
    </tr>
    <tr style="background-color:#FFFFFF" class="extras">
         <td align="left" class="col1" style="padding-left:5px">GPS</td>
         <td align="left" class="col2" style="padding-left:5px">
             <span title="1 extra(s) x $ 13.24 w/o Tax + $ 1.19 Tax = 1 x $ 14.43" style="cursor:pointer">
		$ 13.24             </span>
         </td>
         <td align="right" class="col3" style="padding-right:5px">
             <span title="$ 13.24 w/o Tax + $ 1.19 Tax = $ 14.43" style="cursor:pointer">
		$ 13.24            </span>
         </td>
    </tr>


<!-- TOTAL -->
<tr style="background-color:#343434;color: white" class="total-headers">
    <td align="left" class="col1" colspan="3" style="padding-left:5px"><strong>Total</strong></td>
</tr>
    <tr style="background-color:#FFFFFF">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
            <strong>Sub Total:</strong>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <strong>$ 173.61</strong>
        </td>
    </tr>
    <tr style="background-color:#f2f2f2">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
		Tax (9.00 %):
        </td>
        <td align="right" class="col3" style="padding-right:5px">
		$ 15.62        </td>
    </tr>
<tr style="background-color:#FFFFFF">
    <td align="right" class="col1" style="padding-right:5px" colspan="2">
        <strong>Grand Total:</strong>
    </td>
    <td align="right" class="col3" style="padding-right:5px">
        <strong>$ 189.24</strong>
    </td>
</tr>
    <tr style="background-color:#f2f2f2">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
		Deposit:
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="Cars Deposit ($ 230.00) + Extras Deposit ($ 0.00) = $ 230.00" style="cursor:pointer">
		$ 230.00            </span>
        </td>
    </tr>
</tbody>
</table>

<!-- PAYMENT METHOD DETAILS -->
    <br />
    <table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background:#999999;width:840px;border:none" cellpadding="4" cellspacing="1">
        <tr>
            <td align="left" colspan="2" style="font-weight:bold;background-color:#eeeeee;padding-left:5px">Payment Details</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Pay By</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Bank Transfer</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Payment Details</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Receiver: NATIVE RENTAL, LTD<br />
Account no.: US27 7300 0204 2870 6432<br />
Bank: Bank of America, Inc.</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Transaction ID</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">N/A</td>
        </tr>
    </table>
\',
\'[BLOG_ID]\')';

// This table requires $extAI
$arrPluginReplaceSQL['items'] = "(`item_id`, `item_sku`, `item_page_id`, `partner_id`, `manufacturer_id`, `body_type_id`, `transmission_type_id`, `fuel_type_id`, `model_name`, `item_image_1`, `item_image_2`, `item_image_3`, `demo_item_image_1`, `demo_item_image_2`, `demo_item_image_3`, `mileage`, `fuel_consumption`, `engine_capacity`, `max_passengers`, `max_luggage`, `item_doors`, `min_driver_age`, `price_group_id`, `fixed_rental_deposit`, `units_in_stock`, `max_units_per_booking`, `enabled`, `display_in_slider`, `display_in_item_list`, `display_in_price_table`, `display_in_calendar`, `options_display_mode`, `options_measurement_unit`, `blog_id`) VALUES
(".($extAI+1).", 'IT_1', ".($wpPostsAI+1).", 0, ".($extAI+3).", ".($extAI+2).", ".($extAI+2).", ".($extAI+2).", '207', 'car_peugeot-207.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '39-47 MPG', '1600 ccm', 5, 1, 5, 21, ".($extAI+1).", 110.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+2).", 'IT_2', ".($wpPostsAI+2).", 0, ".($extAI+2).", ".($extAI+2).", ".($extAI+2).", ".($extAI+1).", 'Alto', 'car_suzuki-alto.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '47-59 MPG', '1000 ccm', 5, 1, 5, 21, ".($extAI+2).", 110.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+3).", 'IT_3', ".($wpPostsAI+3).", 0, ".($extAI+4).", ".($extAI+5).", ".($extAI+2).", ".($extAI+2).", 'Vivaro', 'car_opel-vivaro.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-29 MPG', '2000 ccm', 8, 1, 4, 21, ".($extAI+3).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+4).", 'IT_4', ".($wpPostsAI+4).", 0, ".($extAI+3).", ".($extAI+5).", ".($extAI+2).", ".($extAI+2).", 'Boxer', 'car_peugeot-boxer.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-29 MPG', '2200 ccm', 3, 5, 5, 21, ".($extAI+4).", 800.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+5).", 'IT_5', ".($wpPostsAI+5).", 0, ".($extAI+10).", ".($extAI+3).", ".($extAI+3).", ".($extAI+2).", 'A6', 'car_audi-a6.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-34 MPG', '2000 ccm', 5, 1, 5, 21, ".($extAI+5).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+6).", 'IT_6', ".($wpPostsAI+6).", 0, ".($extAI+9).", ".($extAI+3).", ".($extAI+3).", ".($extAI+2).", 'C5', 'car_citroen-c5.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '34-47 MPG', '1600 ccm', 5, 2, 5, 21, ".($extAI+6).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+8).", 'IT_8', ".($wpPostsAI+8).", 0, ".($extAI+4).", ".($extAI+3).", ".($extAI+3).", ".($extAI+1).", 'Astra Sport Tourer', 'car_opel-astra-sport-tourer.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-34 MPG', '1400 ccm', 5, 2, 5, 21, ".($extAI+8).", 220.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+9).", 'IT_9', ".($wpPostsAI+9).", 0, ".($extAI+4).", ".($extAI+3).", ".($extAI+3).", ".($extAI+2).", 'Insignia', 'car_opel-insignia.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-34 MPG', '2000 ccm', 5, 1, 5, 21, ".($extAI+9).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+10).", 'IT_10', ".($wpPostsAI+10).", 0, ".($extAI+7).", ".($extAI+1).", ".($extAI+1).", ".($extAI+1).", '6', 'car_mazda-6.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '21-26 MPG', '2500 ccm', 5, 1, 5, 21, ".($extAI+10).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+12).", 'IT_12', ".($wpPostsAI+12).", 0, ".($extAI+6).", ".($extAI+4).", ".($extAI+3).", ".($extAI+1).", 'ML350', 'car_mercedes-ml350.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '20-24 MPG', '3500 ccm', 5, 2, 5, 21, ".($extAI+12).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+13).", 'IT_13', ".($wpPostsAI+13).", 0, ".($extAI+5).", ".($extAI+4).", ".($extAI+3).", ".($extAI+1).", 'Qashqai', 'car_nissan-qashqai.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-29 MPG', '2000 ccm', 5, 2, 5, 21, ".($extAI+13).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+14).", 'IT_14', ".($wpPostsAI+14).", 0, ".($extAI+8).", ".($extAI+2).", ".($extAI+2).", ".($extAI+2).", 'Fiesta', 'car_ford-fiesta.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '39-47 MPG', '1400 ccm', 5, 1, 5, 21, ".($extAI+14).", 110.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+15).", 'IT_15', ".($wpPostsAI+15).", 0, ".($extAI+5).", ".($extAI+4).", ".($extAI+2).", ".($extAI+2).", 'Qashqai+2', 'car_nissan-qashqai+2.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '26-34 MPG', '2000 ccm', 7, 2, 5, 21, ".($extAI+15).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+16).", 'IT_16', ".($wpPostsAI+16).", 0, ".($extAI+11).", ".($extAI+3).", ".($extAI+2).", ".($extAI+2).", 'Ceed', 'car_kia-ceed.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '29-39 MPG', '1600 ccm', 5, 2, 5, 21, ".($extAI+16).", 110.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]'),
(".($extAI+17).", 'IT_17', ".($wpPostsAI+17).", 0, ".($extAI+12).", ".($extAI+4).", ".($extAI+3).", ".($extAI+2).", 'Touareg', 'car_vw-touareg.jpg', 'car_interior.jpg', 'car_boot.jpg', 1, 1, 1, '', '21-26 MPG', '2500 ccm', 5, 2, 5, 21, ".($extAI+17).", 230.00, 10, 1, 1, 1, 1, 1, 1, 1, '', '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['item_features'] = "(`item_id`, `feature_id`, `blog_id`) VALUES
(".($extAI+13).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+1).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+8).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+3).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+4).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+2).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+7).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+5).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+6).", '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+1).", '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['item_locations'] = "(`item_id`, `location_id`, `location_type`, `blog_id`) VALUES
(".($extAI+11).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+13).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+8).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+1).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+4).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+2).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+6).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+14).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+16).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+10).", ".($extAI+6).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+4).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+3).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+2).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+1).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+5).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+6).", 1, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+4).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+3).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+2).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+1).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+5).", 2, '[BLOG_ID]'),
(".($extAI+12).", ".($extAI+6).", 2, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['locations'] = "(`location_id`, `location_code`, `location_page_id`, `location_name`, `location_image_1`, `location_image_2`, `location_image_3`, `location_image_4`, `demo_location_image_1`, `demo_location_image_2`, `demo_location_image_3`, `demo_location_image_4`, `street_address`, `city`, `state`, `zip_code`, `country`, `phone`, `email`, `pickup_fee`, `return_fee`, `open_mondays`, `open_tuesdays`, `open_wednesdays`, `open_thursdays`, `open_fridays`, `open_saturdays`, `open_sundays`, `open_time_mon`, `open_time_tue`, `open_time_wed`, `open_time_thu`, `open_time_fri`, `open_time_sat`, `open_time_sun`, `close_time_mon`, `close_time_tue`, `close_time_wed`, `close_time_thu`, `close_time_fri`, `close_time_sat`, `close_time_sun`, `lunch_enabled`, `lunch_start_time`, `lunch_end_time`, `afterhours_pickup_allowed`, `afterhours_pickup_location_id`, `afterhours_pickup_fee`, `afterhours_return_allowed`, `afterhours_return_location_id`, `afterhours_return_fee`, `location_order`, `blog_id`) VALUES
(".($extAI+1).", 'LO_1', ".($wpPostsAI+21).", 'San Jose Intl. Airport (SJC)', 'location_sjc_big-map.jpg', 'location_outside-street-view.jpg', 'location_inside-ofice.jpg', 'location_sjc_small-list-map.jpg', 1, 1, 1, 1, '1701 Airport Blvd', 'San Jose', 'CA', '95110', '', '(408) 392-3600', '', 7.44, 7.44, 1, 1, 1, 1, 1, 1, 1, '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', 0, '12:00:00', '13:00:00', 1, 0, 14.88, 1, 0, 14.88, 4, '[BLOG_ID]'),
(".($extAI+2).", 'LO_2', ".($wpPostsAI+22).", 'Oakland Intl. Airport (OAK)', 'location_oak_big-map.jpg', 'location_outside-street-view.jpg', 'location_inside-ofice.jpg', 'location_oak_small-list-map.jpg', 1, 1, 1, 1, '1 Airport Dr', 'Oakland', 'CA', '94621', '', '(510) 563-3300', '', 7.44, 7.44, 1, 1, 1, 1, 1, 1, 1, '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', 0, '12:00:00', '13:00:00', 1, 0, 14.88, 1, 0, 14.88, 3, '[BLOG_ID]'),
(".($extAI+3).", 'LO_3', ".($wpPostsAI+23).", 'San Francisco Intl. Airport (SFO)', 'location_sfo_big-map.jpg', 'location_outside-street-view.jpg', 'location_inside-ofice.jpg', 'location_sfo_small-list-map.jpg', 1, 1, 1, 1, 'Hwy 101', 'San Francisco', 'CA', '94128', '', '(650) 821-8211', '', 16.53, 16.53, 1, 1, 1, 1, 1, 1, 1, '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', 0, '12:00:00', '13:00:00', 1, 0, 23.97, 1, 0, 23.97, 2, '[BLOG_ID]'),
(".($extAI+4).", 'LO_4', ".($wpPostsAI+24).", 'Native Rental HQ', 'location_native-rental-hq_big-map.jpg', 'location_outside-street-view.jpg', 'location_inside-ofice.jpg', 'location_native-rental-hq_small-list-map.jpg', 1, 1, 1, 1, '625 2nd Street', 'San Francisco', 'CA', '94107', '', '(450) 600-4000', '', 0.00, 0.00, 1, 1, 1, 1, 1, 1, 0, '09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00', '10:00:00', '00:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', '15:00:00', '00:00:00', 1, '12:00:00', '13:00:00', 0, 0, 0.00, 0, 0, 0.00, 1, '[BLOG_ID]'),
(".($extAI+5).", 'LO_5', ".($wpPostsAI+25).", 'Los Angeles Intl. Airport (LAX)', 'location_lax_big-map.jpg', 'location_outside-street-view.jpg', 'location_inside-ofice.jpg', 'location_lax_small-list-map.jpg', 1, 1, 1, 1, '1 World Way', 'Los Angeles', 'CA', '90045', '', '(855) 463-5252', '', 16.53, 16.53, 1, 1, 1, 1, 1, 1, 1, '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '06:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', '21:00:00', 0, '12:00:00', '13:00:00', 1, 0, 23.97, 1, 0, 23.97, 5, '[BLOG_ID]'),
(".($extAI+6).", 'LO_6', ".($wpPostsAI+26).", 'Your preferred address', 'location_home-delivery.jpg', '', '', 'location_home-delivery_list.jpg', 1, 0, 0, 1, '', '', '', '', '', '', '', 0.00, 0.00, 1, 1, 1, 1, 1, 1, 1, '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', '19:00:00', 0, '12:00:00', '13:00:00', 1, 0, 7.44, 1, 0, 7.44, 6, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['manufacturers'] = "(`manufacturer_id`, `manufacturer_title`, `manufacturer_logo`, `demo_manufacturer_logo`, `blog_id`) VALUES
(".($extAI+1).", 'Toyota', 'manufacturer_toyota-logo.png', 1, '[BLOG_ID]'),
(".($extAI+2).", 'Suzuki', 'manufacturer_suzuki-logo.png', 1, '[BLOG_ID]'),
(".($extAI+3).", 'Peugeot', 'manufacturer_peugeot-logo.png', 1, '[BLOG_ID]'),
(".($extAI+4).", 'Opel', 'manufacturer_opel-logo.png', 1, '[BLOG_ID]'),
(".($extAI+5).", 'Nissan', 'manufacturer_nissan-logo.png', 1, '[BLOG_ID]'),
(".($extAI+6).", 'Mercedes', 'manufacturer_mercedes-logo.png', 1, '[BLOG_ID]'),
(".($extAI+7).", 'Mazda', 'manufacturer_mazda-logo.png', 1, '[BLOG_ID]'),
(".($extAI+8).", 'Ford', 'manufacturer_ford-logo.png', 1, '[BLOG_ID]'),
(".($extAI+9).", 'Citroen', 'manufacturer_citroen-logo.png', 1, '[BLOG_ID]'),
(".($extAI+10).", 'Audi', 'manufacturer_audi-logo.png', 1, '[BLOG_ID]'),
(".($extAI+11).", 'Kia', 'manufacturer_kia-logo.png', 1, '[BLOG_ID]'),
(".($extAI+12).", 'VW', 'manufacturer_vw-logo.png', 1, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['payment_methods'] = "(`payment_method_id`, `payment_method_code`, `class_name`, `payment_method_name`, `payment_method_email`, `payment_method_description`, `public_key`, `private_key`, `sandbox_mode`, `check_certificate`, `ssl_only`, `online_payment`, `payment_method_enabled`, `payment_method_order`, `expiration_time`, `blog_id`) VALUES
(".($extAI+1).", 'paypal', 'PayPalToFleetManagementTranspiler', 'Online - PayPal', 'yourpaypal@email.com', 'Secure Instant Payment', '', '', 0, 0, 0, 1, 0, 1, 0, '[BLOG_ID]'),
(".($extAI+2).", 'stripe', 'StripeToFleetManagementTranspiler', 'Credit Card (via Stripe.com)', '', '', '', '', 0, 0, 1, 1, 0, 2, 0, '[BLOG_ID]'),
(".($extAI+3).", 'bank', '', 'Bank Transfer', '', 'Receiver: NATIVE RENTAL, LTD\nAccount no.: US27 7300 0204 2870 6432\nBank: Bank of America, Inc.', '', '', 0, 0, 0, 0, 1, 3, 0, '[BLOG_ID]'),
(".($extAI+4).", 'phone', '', 'Pay over the Phone', '', '(450) 600 4000', '', '', 0, 0, 0, 0, 0, 4, 0, '[BLOG_ID]'),
(".($extAI+5).", 'pay-at-pickup', '', 'Pay at Pick-up', '', 'Credit Card Required', '', '', 0, 0, 0, 0, 1, 5, 0, '[BLOG_ID]')";

$arrPluginReplaceSQL['prepayments'] = "(`period_from`, `period_till`, `item_prices_included`, `item_deposits_included`, `extra_prices_included`, `extra_deposits_included`, `pickup_fees_included`, `distance_fees_included`, `return_fees_included`, `prepayment_percentage`, `blog_id`) VALUES
(0, 31622399, 1, 0, 1, 0, 1, 1, 1, 100.00, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['price_groups'] = "(`price_group_id`, `price_group_name`, `partner_id`, `blog_id`) VALUES
(".($extAI+1).", 'Peugeot 207', 0, '[BLOG_ID]'),
(".($extAI+2).", 'Suzuki Alto', 0, '[BLOG_ID]'),
(".($extAI+3).", 'Opel Vivaro', 0, '[BLOG_ID]'),
(".($extAI+4).", 'Peugeot Boxer', 0, '[BLOG_ID]'),
(".($extAI+5).", 'Audi A6', 0, '[BLOG_ID]'),
(".($extAI+6).", 'Citroen C5', 0, '[BLOG_ID]'),
(".($extAI+8).", 'Opel Astra Sport Tourer', 0, '[BLOG_ID]'),
(".($extAI+9).", 'Opel Insignia', 0, '[BLOG_ID]'),
(".($extAI+10).", 'Mazda 6', 0, '[BLOG_ID]'),
(".($extAI+12).", 'Mercedes ML350', 0, '[BLOG_ID]'),
(".($extAI+13).", 'Nissan Qashqai', 0, '[BLOG_ID]'),
(".($extAI+14).", 'Ford Fiesta', 0, '[BLOG_ID]'),
(".($extAI+15).", 'Nissan Qashqai+2', 0, '[BLOG_ID]'),
(".($extAI+16).", 'Kia Ceed', 0, '[BLOG_ID]'),
(".($extAI+17).", 'VW Touareg', 0, '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['price_plans'] = "(`price_plan_id`, `price_group_id`, `coupon_code`, `start_timestamp`, `end_timestamp`, `daily_rate_mon`, `daily_rate_tue`, `daily_rate_wed`, `daily_rate_thu`, `daily_rate_fri`, `daily_rate_sat`, `daily_rate_sun`, `hourly_rate_mon`, `hourly_rate_tue`, `hourly_rate_wed`, `hourly_rate_thu`, `hourly_rate_fri`, `hourly_rate_sat`, `hourly_rate_sun`, `seasonal_price`, `blog_id`) VALUES
(".($extAI+1).", ".($extAI+1).", '', 0, 0, 23.97, 23.97, 23.97, 23.97, 23.97, 23.97, 23.97, 1.21, 1.21, 1.21, 1.21, 1.21, 1.21, 1.21, 0, '[BLOG_ID]'),
(".($extAI+3).", ".($extAI+2).", '', 0, 0, 18.18, 18.18, 18.18, 18.18, 18.18, 18.18, 18.18, 0.92, 0.92, 0.92, 0.92, 0.92, 0.92, 0.92, 0, '[BLOG_ID]'),
(".($extAI+5).", ".($extAI+3).", '', 0, 0, 49.59, 49.59, 49.59, 49.59, 49.59, 49.59, 49.59, 2.25, 2.25, 2.25, 2.25, 2.25, 2.25, 2.25, 0, '[BLOG_ID]'),
(".($extAI+7).", ".($extAI+4).", '', 0, 0, 47.11, 47.11, 47.11, 47.11, 47.11, 47.11, 47.11, 2.50, 2.50, 2.50, 2.50, 2.50, 2.50, 2.50, 0, '[BLOG_ID]'),
(".($extAI+9).", ".($extAI+5).", '', 0, 0, 42.15, 42.15, 42.15, 42.15, 42.15, 42.15, 42.15, 2.13, 2.13, 2.13, 2.13, 2.13, 2.13, 2.13, 0, '[BLOG_ID]'),
(".($extAI+11).", ".($extAI+6).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+15).", ".($extAI+8).", '', 0, 0, 26.45, 26.45, 26.45, 26.45, 26.45, 26.45, 26.45, 1.33, 1.33, 1.33, 1.33, 1.33, 1.33, 1.33, 0, '[BLOG_ID]'),
(".($extAI+17).", ".($extAI+9).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+19).", ".($extAI+10).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+23).", ".($extAI+12).", '', 0, 0, 46.28, 46.28, 46.28, 46.28, 46.28, 46.28, 46.28, 2.33, 2.33, 2.33, 2.33, 2.33, 2.33, 2.33, 0, '[BLOG_ID]'),
(".($extAI+25).", ".($extAI+13).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+27).", ".($extAI+14).", '', 0, 0, 18.18, 18.18, 18.18, 18.18, 18.18, 18.18, 18.18, 0.92, 0.92, 0.92, 0.92, 0.92, 0.92, 0.92, 0, '[BLOG_ID]'),
(".($extAI+29).", ".($extAI+15).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+31).", ".($extAI+16).", '', 0, 0, 26.45, 26.45, 26.45, 26.45, 26.45, 26.45, 26.45, 1.21, 1.21, 1.21, 1.21, 1.21, 1.21, 1.21, 0, '[BLOG_ID]'),
(".($extAI+33).", ".($extAI+17).", '', 0, 0, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 33.88, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 1.71, 0, '[BLOG_ID]'),
(".($extAI+35).", ".($extAI+1).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+36).", ".($extAI+2).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+37).", ".($extAI+5).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+38).", ".($extAI+6).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+39).", ".($extAI+8).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+40).", ".($extAI+9).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+41).", ".($extAI+10).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+42).", ".($extAI+14).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+43).", ".($extAI+16).", 'KNIGHT RIDER', 0, 0, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 10.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 0, '[BLOG_ID]'),
(".($extAI+45).", ".($extAI+4).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]'),
(".($extAI+46).", ".($extAI+17).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]'),
(".($extAI+47).", ".($extAI+3).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]'),
(".($extAI+48).", ".($extAI+15).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]'),
(".($extAI+49).", ".($extAI+13).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]'),
(".($extAI+50).", ".($extAI+12).", 'KNIGHT RIDER', 0, 0, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 20.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 2.00, 0, '[BLOG_ID]')";

$arrPluginReplaceSQL['taxes'] = "(`tax_name`, `location_id`, `location_type`, `tax_percentage`, `blog_id`) VALUES
('Tax', 0, 1, '9.00', '[BLOG_ID]')";

// This table requires $extAI
$arrPluginReplaceSQL['transmission_types'] = "(`transmission_type_id`, `transmission_type_title`, `blog_id`) VALUES
(".($extAI+1).", 'Semi-automatic', '[BLOG_ID]'),
(".($extAI+2).", 'Manual', '[BLOG_ID]'),
(".($extAI+3).", 'Automatic', '[BLOG_ID]')";