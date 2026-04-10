<?php

// php artisan cache:clear
// php artisan route:clear
// php artisan config:clear
// php artisan view:clear
// php artisan optimize:clear
// rm -rf storage/framework/sessions/*
// php artisan optimize
// php artisan config:cache
// php artisan route:cache
// php artisan view:cache
// php artisan queue:restart
// php artisan clear-compiled


// <!-- Highlighted Admin Features
// - Only 2 Types of Product Are Listed (Book, Stationary)
// - List of Implemented payment Gateway using Library : Paytm, .
// - Manage category,
// - Manage sub category,
// - Manage Sub Sub category,
// - Manage Authors,
// - Manage Brands,
// - Manage Publisher,
// - Manage Attributes,
// - Manage Attributes option,
// - Manage Product with Multi Variation,
// - Manage Seo Module,
// - Manage Seller and Seller Product as well,
// - manage Users/Customers,
// - manage Sales, orders, and much more

// Highlight User Features




// Admin Features

// ---------------User manual for Developer only
// in this Project We have used some helper Function :

// GetSetting('option') - option is a key of desired value from setting table,
// GetStatusBadge('status') - using it for getting a badge for all type status values with badge tag,
// AllCountryOption() - Function for getting all country name with value in select option,
// getUser('int id') - id of a perticular user for getting User Details from User Modal,
// -------------------------------------------

// All payment_status - enum
// paid
// unpaid
// refunded
// cancel
// -----------------------------------------
// All payment_method - enum
// cod
// cash
// paytm
// wallet
// partial
// --------------------------------------------------------------
// All Order status enum
// pending - for default order,
// dispatched - when order is dispathceed of courrier,
// outfordelivery - when order is on going by our self dilivery man
// cancelled - when order is canceled by user or by admin
// refunded - when order is refunded by Admin or User Both the order,
// completed - when order is completed,----------------------
// 'pending','dispatched','completed','cancelled','outfordelivery','refunded'

// ----------------------- Flash banner
// banner_sidebar_1
// banner_sidebar_2
// banner_bottom_1
// banner_bottom_2
// banner_bottom_3
// banner_bottom_4

// category_vertical_1
// category_vertical_2
// homepage_category_bottom_1
// homepage_category_bottom_2
// homepage_product_bottom_1
// homepage_product_bottom_2
// homepage_bestseller_1
// -----------------------------Flash end here -->



// <style>
//     /* Hide the video controls within the iframe */
//     .video-container {
//         position: relative;
//         width: 640px;
//         height: 480px;
//     }

//     .video-container iframe {
//         position: absolute;
//         top: 0;
//         left: 0;
//         width: 100%;
//         height: 100%;
//     }

//     .video-container::after {
//         content: "";
//         display: block;
//         position: absolute;
//         bottom: 0px;
//         /* Approximate height of the controls */
//         left: 0;
//         width: 100%;
//         height: 150px;
//         /* Height to cover the controls */
//         background: transparent;
//         /* Same color as the video background */
//     }
// </style>
// <div class="video-container">
//     <iframe src="https://drive.google.com/file/d/1Ptk98QuwVNGsg_z0euz7IASNALNVlwjj/preview" width="640" height="480" allow="autoplay"></iframe>
// </div>
// ------------------------------------------
// if ($request->quantity > $oldQuantity) {
//     $stockUpdateResult = decreaseProductQuantity($item->product_id, $item->variation_id, $oldQuantity, $request->quantity);
// } elseif ($request->quantity < $oldQuantity) {
//     $stockUpdateResult = increaseProductQuantity(
//         $item->product_id,
//         $item->variation_id,
//         $oldQuantity,
//         $request->quantity
//     );
// }
// if ($stockUpdateResult['status'] !== 1) {
//     return response()->json(['status' => 0, 'message' => $stockUpdateResult['message']]);
// }

// return ['status' => 0, 'message' => 'Product not found during decrement'];


// all Crud operation button is btn-sm & in right side for all forms and
// secondary - Cancel
// primary - Add
// success - update
// danger - delete
// info - print
// warning - imageFetch Laravel File manager

// use this for return back with uploaded image
// <div class="col-md-2 form-group" id="holder">
//     <label for="name"> Preview</label>
//     <img src="{{ asset('/') }}storage/{{ old('photo') }}" alt="" height="100px">
// </div>

// ---------------------------------------
// sendNotify('product_review', 'info', 'User wrote a review for product : ' . $sanitizedReview, 'low', 'admin', $request->product_id);
// // function name ('for', 'type', 'message', 'priority', 'visible for', 'target id');
// user side ---------------------
// 1. product_review,
// 2. post_comment,
// 3. order_placed,
// 4. order_cancel,
// 5. order_refund,
// 6. seller_onboard,
// 7. user_onboard,
// 8. rent_request,
// 9. ticket,
// user side --------------------------

// Admin side --------------------------

//  1. cache_clear,
//  2. product_review,
//  3. stock_mail,
//  4. csv_export,
//  5. order_update,
//  6. order_refund,
//  7. order_placed,

// Admin side --------------------------
