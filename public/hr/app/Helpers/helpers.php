<?php

use App\Models\Users;
use App\Models\Seller;
use App\Models\Wallet;
use GuzzleHttp\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Variant;
use App\Models\StaffLog;
use App\Models\Supplier;
use App\Models\WalletTxn;
use App\Models\MasterOrder;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\OtpEntries;
use App\Models\Setting;
use App\Models\ShippingInvoice;
use App\Models\ShortUrl;
use App\Models\SupplierTxn;
use Illuminate\Support\Str;
use App\Models\SupplierWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Models\Organization;

if (!function_exists('encrypt')) {
    function encrypt($id)
    {
        return Crypt::encryptString($id);
    }
}

if (!function_exists('decrypt')) {
    function decrypt($id)
    {
        return Crypt::decryptString($id);
    }
}

if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($name, $modelClass, $currentId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $query = $modelClass::where('slug', $slug);
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }
        $count = 1;
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count;
            $query = $modelClass::where('slug', $slug);
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }
            $count++;
        }
        return $slug;
    }
}

if (!function_exists('staffLog')) {
    function staffLog($modal, $modal_id, $type, $log)
    {
        $logStaff = [
            'staff_id' => Auth::guard('admin')->id(),
            'table' => $modal,
            'primary_id' => $modal_id,
            'type' => $type ?? 'other',
            'log' => Auth::guard('admin')->user()->name . ' ' . $log,
        ];
        StaffLog::create($logStaff);
        return true;
    }
}

if (!function_exists('GlobalSetting')) {
    // get setting value using key
    function GlobalSetting($key) // getting key value for setting
    {
        $value = Setting::where('option', $key)->first();
        return $value->value ?? null;
    }
}

if (!function_exists('AllCountryOption')) {
    function AllCountryOption()
    {
        $country = '<option value="Afghanistan">Afghanistan</option>
            <option value="Åland Islands">Åland Islands</option>
            <option value="Albania">Albania</option>
            <option value="Algeria">Algeria</option>
            <option value="American Samoa">American Samoa</option>
            <option value="Andorra">Andorra</option>
            <option value="Angola">Angola</option>
            <option value="Anguilla">Anguilla</option>
            <option value="Antarctica">Antarctica</option>
            <option value="Antigua and Barbuda">Antigua and Barbuda</option>
            <option value="Argentina">Argentina</option>
            <option value="Armenia">Armenia</option>
            <option value="Aruba">Aruba</option>
            <option value="Australia">Australia</option>
            <option value="Austria">Austria</option>
            <option value="Azerbaijan">Azerbaijan</option>
            <option value="Bahamas">Bahamas</option>
            <option value="Bahrain">Bahrain</option>
            <option value="Bangladesh">Bangladesh</option>
            <option value="Barbados">Barbados</option>
            <option value="Belarus">Belarus</option>
            <option value="Belgium">Belgium</option>
            <option value="Belize">Belize</option>
            <option value="Benin">Benin</option>
            <option value="Bermuda">Bermuda</option>
            <option value="Bhutan">Bhutan</option>
            <option value="Bolivia">Bolivia</option>
            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
            <option value="Botswana">Botswana</option>
            <option value="Bouvet Island">Bouvet Island</option>
            <option value="Brazil">Brazil</option>
            <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
            <option value="Brunei Darussalam">Brunei Darussalam</option>
            <option value="Bulgaria">Bulgaria</option>
            <option value="Burkina Faso">Burkina Faso</option>
            <option value="Burundi">Burundi</option>
            <option value="Cambodia">Cambodia</option>
            <option value="Cameroon">Cameroon</option>
            <option value="Canada">Canada</option>
            <option value="Cape Verde">Cape Verde</option>
            <option value="Cayman Islands">Cayman Islands</option>
            <option value="Central African Republic">Central African Republic</option>
            <option value="Chad">Chad</option>
            <option value="Chile">Chile</option>
            <option value="China">China</option>
            <option value="Christmas Island">Christmas Island</option>
            <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
            <option value="Colombia">Colombia</option>
            <option value="Comoros">Comoros</option>
            <option value="Congo">Congo</option>
            <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
            <option value="Cook Islands">Cook Islands</option>
            <option value="Costa Rica">Costa Rica</option>
            <option value="Cote Divoire">Cote D ivoire</option>
            <option value="Croatia">Croatia</option>
            <option value="Cuba">Cuba</option>
            <option value="Cyprus">Cyprus</option>
            <option value="Czech Republic">Czech Republic</option>
            <option value="Denmark">Denmark</option>
            <option value="Djibouti">Djibouti</option>
            <option value="Dominica">Dominica</option>
            <option value="Dominican Republic">Dominican Republic</option>
            <option value="Ecuador">Ecuador</option>
            <option value="Egypt">Egypt</option>
            <option value="El Salvador">El Salvador</option>
            <option value="Equatorial Guinea">Equatorial Guinea</option>
            <option value="Eritrea">Eritrea</option>
            <option value="Estonia">Estonia</option>
            <option value="Ethiopia">Ethiopia</option>
            <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
            <option value="Faroe Islands">Faroe Islands</option>
            <option value="Fiji">Fiji</option>
            <option value="Finland">Finland</option>
            <option value="France">France</option>
            <option value="French Guiana">French Guiana</option>
            <option value="French Polynesia">French Polynesia</option>
            <option value="French Southern Territories">French Southern Territories</option>
            <option value="Gabon">Gabon</option>
            <option value="Gambia">Gambia</option>
            <option value="Georgia">Georgia</option>
            <option value="Germany">Germany</option>
            <option value="Ghana">Ghana</option>
            <option value="Gibraltar">Gibraltar</option>
            <option value="Greece">Greece</option>
            <option value="Greenland">Greenland</option>
            <option value="Grenada">Grenada</option>
            <option value="Guadeloupe">Guadeloupe</option>
            <option value="Guam">Guam</option>
            <option value="Guatemala">Guatemala</option>
            <option value="Guernsey">Guernsey</option>
            <option value="Guinea">Guinea</option>
            <option value="Guinea-bissau">Guinea-bissau</option>
            <option value="Guyana">Guyana</option>
            <option value="Haiti">Haiti</option>
            <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
            <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
            <option value="Honduras">Honduras</option>
            <option value="Hong Kong">Hong Kong</option>
            <option value="Hungary">Hungary</option>
            <option value="Iceland">Iceland</option>
            <option selected value="India">India</option>
            <option value="Indonesia">Indonesia</option>
            <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
            <option value="Iraq">Iraq</option>
            <option value="Ireland">Ireland</option>
            <option value="Isle of Man">Isle of Man</option>
            <option value="Israel">Israel</option>
            <option value="Italy">Italy</option>
            <option value="Jamaica">Jamaica</option>
            <option value="Japan">Japan</option>
            <option value="Jersey">Jersey</option>
            <option value="Jordan">Jordan</option>
            <option value="Kazakhstan">Kazakhstan</option>
            <option value="Kenya">Kenya</option>
            <option value="Kiribati">Kiribati</option>
            <option value="Korea, Democratic People`s Republic of">Korea, Democratic People`s Republic of</option>
            <option value="Korea, Republic of">Korea, Republic of</option>
            <option value="Kuwait">Kuwait</option>
            <option value="Kyrgyzstan">Kyrgyzstan</option>
            <option value="Lao People`s Democratic Republic">Lao People`s Democratic Republic</option>
            <option value="Latvia">Latvia</option>
            <option value="Lebanon">Lebanon</option>
            <option value="Lesotho">Lesotho</option>
            <option value="Liberia">Liberia</option>
            <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
            <option value="Liechtenstein">Liechtenstein</option>
            <option value="Lithuania">Lithuania</option>
            <option value="Luxembourg">Luxembourg</option>
            <option value="Macao">Macao</option>
            <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
            <option value="Madagascar">Madagascar</option>
            <option value="Malawi">Malawi</option>
            <option value="Malaysia">Malaysia</option>
            <option value="Maldives">Maldives</option>
            <option value="Mali">Mali</option>
            <option value="Malta">Malta</option>
            <option value="Marshall Islands">Marshall Islands</option>
            <option value="Martinique">Martinique</option>
            <option value="Mauritania">Mauritania</option>
            <option value="Mauritius">Mauritius</option>
            <option value="Mayotte">Mayotte</option>
            <option value="Mexico">Mexico</option>
            <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
            <option value="Moldova, Republic of">Moldova, Republic of</option>
            <option value="Monaco">Monaco</option>
            <option value="Mongolia">Mongolia</option>
            <option value="Montenegro">Montenegro</option>
            <option value="Montserrat">Montserrat</option>
            <option value="Morocco">Morocco</option>
            <option value="Mozambique">Mozambique</option>
            <option value="Myanmar">Myanmar</option>
            <option value="Namibia">Namibia</option>
            <option value="Nauru">Nauru</option>
            <option value="Nepal">Nepal</option>
            <option value="Netherlands">Netherlands</option>
            <option value="Netherlands Antilles">Netherlands Antilles</option>
            <option value="New Caledonia">New Caledonia</option>
            <option value="New Zealand">New Zealand</option>
            <option value="Nicaragua">Nicaragua</option>
            <option value="Niger">Niger</option>
            <option value="Nigeria">Nigeria</option>
            <option value="Niue">Niue</option>
            <option value="Norfolk Island">Norfolk Island</option>
            <option value="Northern Mariana Islands">Northern Mariana Islands</option>
            <option value="Norway">Norway</option>
            <option value="Oman">Oman</option>
            <option value="Pakistan">Pakistan</option>
            <option value="Palau">Palau</option>
            <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
            <option value="Panama">Panama</option>
            <option value="Papua New Guinea">Papua New Guinea</option>
            <option value="Paraguay">Paraguay</option>
            <option value="Peru">Peru</option>
            <option value="Philippines">Philippines</option>
            <option value="Pitcairn">Pitcairn</option>
            <option value="Poland">Poland</option>
            <option value="Portugal">Portugal</option>
            <option value="Puerto Rico">Puerto Rico</option>
            <option value="Qatar">Qatar</option>
            <option value="Reunion">Reunion</option>
            <option value="Romania">Romania</option>
            <option value="Russian Federation">Russian Federation</option>
            <option value="Rwanda">Rwanda</option>
            <option value="Saint Helena">Saint Helena</option>
            <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
            <option value="Saint Lucia">Saint Lucia</option>
            <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
            <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
            <option value="Samoa">Samoa</option>
            <option value="San Marino">San Marino</option>
            <option value="Sao Tome and Principe">Sao Tome and Principe</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="Senegal">Senegal</option>
            <option value="Serbia">Serbia</option>
            <option value="Seychelles">Seychelles</option>
            <option value="Sierra Leone">Sierra Leone</option>
            <option value="Singapore">Singapore</option>
            <option value="Slovakia">Slovakia</option>
            <option value="Slovenia">Slovenia</option>
            <option value="Solomon Islands">Solomon Islands</option>
            <option value="Somalia">Somalia</option>
            <option value="South Africa">South Africa</option>
            <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
            <option value="Spain">Spain</option>
            <option value="Sri Lanka">Sri Lanka</option>
            <option value="Sudan">Sudan</option>
            <option value="Suriname">Suriname</option>
            <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
            <option value="Swaziland">Swaziland</option>
            <option value="Sweden">Sweden</option>
            <option value="Switzerland">Switzerland</option>
            <option value="Syrian Arab Republic">Syrian Arab Republic</option>
            <option value="Taiwan">Taiwan</option>
            <option value="Tajikistan">Tajikistan</option>
            <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
            <option value="Thailand">Thailand</option>
            <option value="Timor-leste">Timor-leste</option>
            <option value="Togo">Togo</option>
            <option value="Tokelau">Tokelau</option>
            <option value="Tonga">Tonga</option>
            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
            <option value="Tunisia">Tunisia</option>
            <option value="Turkey">Turkey</option>
            <option value="Turkmenistan">Turkmenistan</option>
            <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
            <option value="Tuvalu">Tuvalu</option>
            <option value="Uganda">Uganda</option>
            <option value="Ukraine">Ukraine</option>
            <option value="United Arab Emirates">United Arab Emirates</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="United States">United States</option>
            <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
            <option value="Uruguay">Uruguay</option>
            <option value="Uzbekistan">Uzbekistan</option>
            <option value="Vanuatu">Vanuatu</option>
            <option value="Venezuela">Venezuela</option>
            <option value="Viet Nam">Viet Nam</option>
            <option value="Virgin Islands, British">Virgin Islands, British</option>
            <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
            <option value="Wallis and Futuna">Wallis and Futuna</option>
            <option value="Western Sahara">Western Sahara</option>
            <option value="Yemen">Yemen</option>
            <option value="Zambia">Zambia</option>
            <option value="Zimbabwe">Zimbabwe</option>
            </select>';
        echo $country;
    }
}

if (!function_exists('GetStatusBadge')) {
    function GetStatusBadge($status)
    {
        if ($status == 'active') {
            $status = '<span class="badge badge-sm badge-success">Active </span>';
        } elseif ($status == 'inactive') {
            $status = '<span class="badge badge-sm badge-danger">In Active </span>';
            // order status
        } elseif ($status == 'pending') {
            $status = '<span class="badge badge-sm badge-secondary">Pending </span>';
        } elseif ($status == 'dispatched') {
            $status = '<span class="badge badge-sm badge-success">Dispatched </span>';
        } elseif ($status == 'outfordelivery') {
            $status = '<span class="badge badge-sm badge-info">Out for Delivery </span>';
        } elseif ($status == 'cancelled') {
            $status = '<span class="badge badge-sm badge-danger">cancelled </span>';
        } elseif ($status == 'refunded') {
            $status = '<span class="badge badge-sm badge-secondary">refunded </span>';
        } elseif ($status == 'completed') {
            $status = '<span class="badge badge-sm badge-success">Completed </span>';
        } elseif ($status == 'partial') {
            $status = '<span class="badge badge-sm badge-primary">Partial </span>';
        }
        // blog post status
        elseif ($status == 'draft') {
            $status = '<span class="badge badge-sm badge-primary">Draft </span>';
        } elseif ($status == 'publish') {
            $status = '<span class="badge badge-sm badge-success">Publish </span>';
        }
        // payment method status
        elseif ($status == 'paid') {
            $status = '<span class="badge badge-sm badge-success">Paid </span>';
        } elseif ($status == 'unpaid') {
            $status = '<span class="badge badge-sm badge-danger">Unpaid </span>';
        } elseif ($status == 'cancel') {
            $status = '<span class="badge badge-sm badge-danger">Cancel </span>';
        }
        // rent or refund requests status
        elseif ($status == 'requested') {
            $status = '<span class="badge badge-sm badge-dark">Requested </span>';
        } elseif ($status == 'approved') {
            $status = '<span class="badge badge-sm badge-success">Approved </span>';
        } elseif ($status == 'received') {
            $status = '<span class="badge badge-sm badge-success">Received </span>';
        } elseif ($status == 'rejected') {
            $status = '<span class="badge badge-sm badge-danger">Rejected </span>';
        } elseif ($status == 'open') {
            $status = '<span class="badge badge-sm badge-success">Open </span>';
        } elseif ($status == 'closed') {
            $status = '<span class="badge badge-sm badge-primary">Closed </span>';
        } elseif ($status == 'close') {
            $status = '<span class="badge badge-sm badge-primary">Closed </span>';
        } elseif ($status == 'freez') {
            $status = '<span class="badge badge-sm badge-danger">Freez </span>';
        } elseif ($status == 'hold') {
            $status = '<span class="badge badge-sm badge-warning">On Hold </span>';
        } elseif ($status == 'returned') {
            $status = '<span class="badge badge-sm badge-success">Returned </span>';
        } elseif ($status == 'rented') {
            $status = '<span class="badge badge-sm badge-primary">Rented </span>';
        } elseif ($status == 'not_rented') {
            $status = '<span class="badge badge-sm badge-warning text-dark">Not Rented </span>';
        } elseif ($status == 'verified') {
            $status = '<span class="badge badge-sm badge-success">Verified</span>';
        } elseif ($status == 'notverified') {
            $status = '<span class="badge badge-sm badge-danger">Not Verified </span>';
        } elseif ($status == 'registering') {
            $status = '<span class="badge badge-sm badge-info">Registering On Going </span>';
        } elseif ($status == 'allIndia') {
            $status = '<span class="badge badge-sm badge-warning">All India  </span>';
        } elseif ($status == 'unapprove') {
            $status = '<span class="badge badge-sm badge-danger">UnApprove Leave Taken  </span>';
        }
        // ------------------------------ Project Status
        elseif ($status == 'processing') {
            $status = '<span class="badge badge-sm badge-primary">Processing  </span>';
        } elseif ($status == 'not_started') {
            $status = '<span class="badge badge-sm badge-secondary">Not Started  </span>';
        } elseif ($status == 'in_progress') {
            $status = '<span class="badge badge-sm badge-primary">In Progress  </span>';
        } elseif ($status == 'complete') {
            $status = '<span class="badge badge-sm badge-success">Completed  </span>';
        } elseif ($status == 'on_hold') {
            $status = '<span class="badge badge-sm badge-warning">On Hold  </span>';
        } elseif ($status == 'incomplete') {
            $status = '<span class="badge badge-sm badge-danger">In Complete  </span>';
        } elseif ($status == 'low') {
            $status = '<span class="badge badge-sm badge-info">Low  </span>';
        } elseif ($status == 'medium') {
            $status = '<span class="badge badge-sm badge-warning">Medium  </span>';
        } elseif ($status == 'high') {
            $status = '<span class="badge badge-sm badge-danger">High  </span>';
        }
        // --------------------------
        else {
            $status = '<span class="badge badge-sm badge-dark"> No Status </span>';
        }

        return $status;
    }
}

if (!function_exists('increaseProductQuantity')) {
    function increaseProductQuantity($productId, $variationId, $ItemQuantity, $quantity)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($productId);
            if ($product) {
                if ($variationId !== 'no') {
                    $variation = Variant::find($variationId);
                    if ($variation) {
                        $oldStock = $variation->stock;
                        $quantityChange = $quantity - $ItemQuantity;
                        $stockDifference = abs($quantityChange);
                        $finalStock = $oldStock + $stockDifference;
                        $variation->update(['stock' => $finalStock]);
                        Log::channel('product_stock')->info(json_encode([
                            'message' => 'Product Variation Increase',
                            'type' => 'increase',
                            'old_stock' => $oldStock,
                            'new_stock' => $finalStock,
                            'product_id' => $productId,
                            'variation_id' => $variationId,
                            'quantity_change' => $quantityChange,
                            'request_quantity' => $quantity,
                        ]));
                    } else {
                        Log::info('Product Variation not found during increase: proID - ' . $productId . ' & varID - ' . $variationId);
                        DB::rollBack();
                        return ['status' => 0, 'message' => 'Product Variation not found during increase: proID - ' . $productId . ' & varID - ' . $variationId];
                    }
                } else {
                    $oldStock = $product->stock;
                    $quantityChange = $quantity - $ItemQuantity;
                    $stockDifference = abs($quantityChange);
                    $finalStock = $oldStock + $stockDifference;
                    $product->update(['stock' => $finalStock]);
                    Log::channel('product_stock')->info(json_encode([
                        'message' => 'Product Increase',
                        'type' => 'increase',
                        'old_stock' => $oldStock,
                        'new_stock' => $finalStock,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity_change' => $quantityChange,
                        'request_quantity' => $quantity,
                    ]));
                }
                DB::commit();
                return ['status' => 1];
            } else {
                Log::info('Product not found during increase: proID - ' . $productId);
                DB::rollBack();
                return ['status' => 0, 'message' => 'Product not found during increase: proID - ' . $productId];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update failed during increase: ' . $e->getMessage());
            return ['status' => 0, 'message' => 'Stock update failed during increase: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('decreaseProductQuantity')) {
    function decreaseProductQuantity($productId, $variationId, $ItemQuantity, $quantity)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($productId);
            if ($product) {
                if ($variationId !== 'no') {
                    $variation = Variant::find($variationId);
                    if ($variation) {
                        $oldStock = $variation->stock;
                        $quantityChange = $quantity - $ItemQuantity;
                        $stockDifference = abs($quantityChange);
                        if ($oldStock < $stockDifference) {
                            DB::rollBack();
                            Log::info('Product variation Insufficient stock for the ' . $variationId . ' - Current quantity' . $oldStock . ' & Requested quantity' . $stockDifference);
                            return ['status' => 0, 'message' => 'Requested variation quantity cannot be decrease due to low stock levels.'];
                        }
                        $finalStock = $oldStock - $stockDifference;
                        $variation->update(['stock' => $finalStock]);
                        Log::channel('product_stock')->info(json_encode([
                            'message' => 'Product Variation Decrease',
                            'type' => 'decrease',
                            'old_stock' => $oldStock,
                            'new_stock' => $finalStock,
                            'product_id' => $productId,
                            'variation_id' => $variationId,
                            'quantity_change' => $quantityChange,
                            'request_quantity' => $quantity,
                        ]));
                    } else {
                        Log::info('Product Variation not found during decrement: proID - ' . $productId . ' & varID - ' . $variationId);
                        DB::rollBack();
                        return ['status' => 0, 'message' => 'Product Variation not found during decrement'];
                    }
                } else {
                    $oldStock = $product->stock;
                    $quantityChange = $quantity - $ItemQuantity;
                    $stockDifference = abs($quantityChange);
                    if ($oldStock < $stockDifference) {
                        DB::rollBack();
                        Log::info('Product Insufficient stock for the ' . $productId . ' - Current quantity' . $oldStock . ' & Requested quantity' . $stockDifference);

                        return ['status' => 0, 'message' => 'Requested quantity not available in stock'];
                    }
                    $finalStock = $oldStock - $stockDifference;
                    $result = $product->update(['stock' => $finalStock]);
                    Log::channel('product_stock')->info(json_encode([
                        'message' => 'Product Decrease',
                        'type' => 'decrease',
                        'old_stock' => $oldStock,
                        'new_stock' => $finalStock,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity_change' => $quantityChange,
                        'request_quantity' => $quantity,
                    ]));
                }
                DB::commit();

                return ['status' => 1];
            } else {
                Log::info('Product not found during decrement: proID - ' . $productId);
                DB::rollBack();

                return ['status' => 0, 'message' => 'Product not found during decrement'];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update failed during increase: ' . $e->getMessage());
            return ['status' => 0, 'message' => 'Stock update failed during increase'];
        }
    }
}

if (!function_exists('CreateOrderSellerInvoice')) {
    function CreateOrderSellerInvoice($masterOrderId, $masterOrderNumber, $source, $type)
    {
        $allOrderItem = OrderItem::where('order_master_id', $masterOrderId)
            ->select('seller_id', DB::raw('MAX(user_id) as user_id'), DB::raw('SUM(IF(purchase_method = "on_rent", security_price * quantity, sell_price * quantity)) AS seller_subtotal'))
            ->groupBy('seller_id')
            ->get();
        foreach ($allOrderItem as $item) {
            $itemIds = OrderItem::where('order_master_id', $masterOrderId)
                ->where('seller_id', $item->seller_id)
                ->pluck('id')
                ->toArray();
            $seller = Seller::find($item->seller_id);
            $masterOrder = MasterOrder::find($masterOrderId);
            if (!$seller) {
                CreateOrderLog($masterOrderId, null, 'order', 'Seller not found for seller_id: ' . $item->seller_id, 'error');
                continue;
            }
            $sellerPrefix = $seller->invoice_prefix ?? $item->seller_id . 'NOPREFIX';
            if (!$sellerPrefix) {
                CreateOrderLog($masterOrderId, null, 'order', 'Seller prefix is invalid or missing for seller_id: ' . $item->seller_id, 'error');
                continue;
            }
            $lastInvoiceNumber = Invoice::where('seller_id', $item->seller_id)->latest()->value('invoice_no');
            if ($lastInvoiceNumber !== null) {
                $invoiceNumberSuffix = intval(substr($lastInvoiceNumber, strlen($sellerPrefix))) + 1;
            } else {
                $invoiceNumberSuffix = 1;
            }
            $newInvoiceNumber = $sellerPrefix . str_pad($invoiceNumberSuffix, 4, '0', STR_PAD_LEFT);
            try {
                Invoice::create([
                    'itemIds' => implode(',', $itemIds),
                    'invoice_no' => $newInvoiceNumber,
                    'order_id' => $masterOrderId,
                    'order_no' => $masterOrderNumber,
                    'seller_id' => $item->seller_id,
                    'store_id' => $masterOrder->store_id,
                    'user_id' => $item->user_id,
                    'invoice_type' => $source,
                    'invoice_source' => $source,
                    'invoice_for' => $type,
                    'subtotal' => $item->seller_subtotal,
                    'total' => $item->seller_subtotal,
                    'grandtotal' => $item->seller_subtotal,
                    'status' => 'closed',
                    'created_at' => now(),
                ]);
                CreateOrderLog($masterOrderId, null, 'order', 'Invoice Created Successfully for ' . $seller->name . ', & Invoice No - ' . $newInvoiceNumber, 'info');
            } catch (\Exception $e) {
                CreateOrderLog($masterOrderId, null, 'order', 'Invoice creation failed for ' . $seller->name . ', Error: ' . $e->getMessage(), 'error');
            }
        }

        return true;
    }
}

// --------------------
if (!function_exists('decreaseProductPreOrderQuantity')) {
    function decreaseProductPreOrderQuantity($productId, $variationId, $ItemQuantity, $quantity)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($productId);
            if ($product) {
                if ($variationId !== 'no') {
                    $variation = Variant::find($variationId);
                    if ($variation) {
                        $oldStock = $variation->stock;
                        $quantityChange = $quantity - $ItemQuantity;
                        $stockDifference = abs($quantityChange);
                        if ($oldStock < $stockDifference) {
                            Log::info('Product variation Insufficient stock for the ' . $variationId . ' - Current quantity' . $oldStock . ' & Requested quantity' . $stockDifference);
                        }
                        $finalStock = $oldStock - $stockDifference;
                        $variation->update(['stock' => $finalStock]);
                        Log::channel('product_stock')->info(json_encode([
                            'message' => 'Product Variation Decrease',
                            'type' => 'decrease',
                            'old_stock' => $oldStock,
                            'new_stock' => $finalStock,
                            'product_id' => $productId,
                            'variation_id' => $variationId,
                            'quantity_change' => $quantityChange,
                            'request_quantity' => $quantity,
                        ]));
                    } else {
                        Log::info('Product Variation not found during decrement: proID - ' . $productId . ' & varID - ' . $variationId);
                        DB::rollBack();
                        return ['status' => 0, 'message' => 'Product Variation not found during decrement'];
                    }
                } else {
                    $oldStock = $product->stock;
                    $quantityChange = $quantity - $ItemQuantity;
                    $stockDifference = abs($quantityChange);
                    if ($oldStock < $stockDifference) {
                        Log::info('Product Insufficient stock for the ' . $productId . ' - Current quantity' . $oldStock . ' & Requested quantity' . $stockDifference);
                    }
                    $finalStock = $oldStock - $stockDifference;
                    $result = $product->update(['stock' => $finalStock]);
                    Log::channel('product_stock')->info(json_encode([
                        'message' => 'Product Decrease',
                        'type' => 'decrease',
                        'old_stock' => $oldStock,
                        'new_stock' => $finalStock,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity_change' => $quantityChange,
                        'request_quantity' => $quantity,
                    ]));
                }
                DB::commit();
                return ['status' => 1];
            } else {
                Log::info('Product not found during decrement: proID - ' . $productId);
                DB::rollBack();
                return ['status' => 0, 'message' => 'Product not found during decrement'];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update failed during increase: ' . $e->getMessage());
            return ['status' => 0, 'message' => 'Stock update failed during increase'];
        }
    }
}

// .-----------------------------------------
if (!function_exists('CreatePreBookingSellerInvoice')) {
    function CreatePreBookingSellerInvoice($selectedItemsArray, $orderId, $orderNo)
    {
        $allOrderItem = OrderItem::whereIn('id', $selectedItemsArray)
            ->select('seller_id', DB::raw('MAX(user_id) as user_id'), DB::raw('SUM(IF(purchase_method = "on_rent", security_price * quantity, sell_price * quantity)) AS seller_subtotal'))
            ->groupBy('seller_id')
            ->get();
        foreach ($allOrderItem as $item) {
            $itemIds = OrderItem::whereIn('id', $selectedItemsArray)
                ->where('seller_id', $item->seller_id)
                ->pluck('id')
                ->toArray();
            $seller = Seller::find($item->seller_id);
            $masterOrder = MasterOrder::find($orderId);
            if (!$seller) {
                CreateOrderLog($orderId, null, 'order', 'Seller not found for seller_id: ' . $item->seller_id, 'error');
                continue;
            }
            $sellerPrefix = $seller->invoice_prefix ?? $item->seller_id . 'NOPREFIX';
            if (!$sellerPrefix) {
                CreateOrderLog($orderId, null, 'order', 'Seller prefix is invalid or missing for seller_id: ' . $item->seller_id, 'error');
                continue;
            }
            $lastInvoiceNumber = DB::table('invoice')->where('seller_id', $item->seller_id)->latest()->value('invoice_no');
            if ($lastInvoiceNumber !== null) {
                $invoiceNumberSuffix = intval(substr($lastInvoiceNumber, strlen($sellerPrefix))) + 1;
            } else {
                $invoiceNumberSuffix = 1;
            }
            $newInvoiceNumber = $sellerPrefix . str_pad($invoiceNumberSuffix, 4, '0', STR_PAD_LEFT);
            try {
                $sellerInvoice = [
                    'itemIds' => implode(',', $itemIds),
                    'invoice_no' => $newInvoiceNumber,
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'seller_id' => $item->seller_id,
                    'store_id' => $masterOrder->store_id,
                    'user_id' => $item->user_id,
                    'invoice_type' => 'prebooking',
                    'invoice_source' => 'prebooking',
                    'invoice_for' => 'seller',
                    'subtotal' => $item->seller_subtotal,
                    'total' => $item->seller_subtotal,
                    'grandtotal' => $item->seller_subtotal,
                    'status' => 'closed',
                ];
                Invoice::create($sellerInvoice);
                CreateOrderLog($orderId, null, 'order', 'Invoice Created Successfully for ' . $seller->name . ', & Invoice No - ' . $newInvoiceNumber, 'info');
            } catch (\Exception $e) {
                CreateOrderLog($orderId, null, 'order', 'Invoice creation failed for ' . $seller->name . ', Error: ' . $e->getMessage(), 'error');
            }
        }
        return true;
    }
}

// --------------------
if (!function_exists('CreateWallet')) {
    function CreateWallet($user_id)
    {
        $Wallet_data = [
            'user_id' => $user_id,
            'balance' => '0',
            'last_txn_amount' => '0',
            'status' => 'active',
            'created_at' => now(),
        ];
        $walletResult = Wallet::create($Wallet_data);
        if ($walletResult) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('CreateOrderLog')) {
    function CreateOrderLog($orderId, $itemId, $effective, $log, $type = null)
    {
        $orderLog = [
            'order_id' => $orderId,
            'item_id' => $itemId,
            'effective' => $effective,
            'log' => $log,
            'type' => $type,
            'staff_id' => Auth::guard('admin')->id(),
        ];
        DB::table('order_log')->insertGetId($orderLog);
    }
}

// ------------------------------Credit Money into Wallet
if (!function_exists('DebitUserWalletTxn')) {
    function DebitUserWalletTxn($userId, $amount, $log, $method)
    {
        try {
            DB::beginTransaction();
            $user = Users::find($userId);
            if (!$user) {
                throw new Exception("User not found.");
            }
            $oldWallet = Wallet::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            if (!$oldWallet) {
                throw new Exception("Active wallet not found for the user.");
            }
            $Wallet_update = [
                'balance' => $oldWallet->balance - $amount,
                'last_txn_id' => Auth::guard('admin')->id(),
                'last_txn_date' => now(),
                'last_txn_amount' => $amount,
                'updated_at' => now(),
            ];
            $Wallet_Transaction = [
                'wallet_id' => $oldWallet->id,
                'user_id' => $user->id,
                'debit_amount' => $amount,
                'credit_amount' => null,
                'rest_balance' => $oldWallet->balance - $amount,
                'txn_date' => now(),
                'txn_for' => $log ?? 'Debit Money into Wallet',
                'txn_method' => $method ?? 'Wallet Transaction',
                'txn_byuser_id' => Auth::guard('admin')->id(),
                'comment' => $log,
                'created_at' => now(),
            ];
            $oldWallet->update($Wallet_update);
            $TxnResult = WalletTxn::create($Wallet_Transaction);
            staffLog('wallet', $oldWallet->id, 'update', "User wallet money updated");
            staffLog('wallet_txn', $TxnResult->id, 'create', 'User wallet txn created, Debit money : ' . $amount . ' and new balance: ' . ($amount - $oldWallet->balance));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}

// ---------------------------------- Debit Money into Wallet
if (!function_exists('CreditUserWalletTxn')) {
    function CreditUserWalletTxn($userId, $amount, $log, $method)
    {
        try {
            DB::beginTransaction();
            $user = Users::find($userId);
            if (!$user) {
                throw new Exception("User not found.");
            }
            $oldWallet = Wallet::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            if (!$oldWallet) {
                throw new Exception("Active wallet not found for the user.");
            }
            $Wallet_update = [
                'balance' => $oldWallet->balance + $amount,
                'last_txn_id' => Auth::guard('admin')->id(),
                'last_txn_date' => now(),
                'last_txn_amount' => $amount,
                'updated_at' => now(),
            ];
            $Wallet_Transaction = [
                'wallet_id' => $oldWallet->id,
                'user_id' => $user->id,
                'debit_amount' => null,
                'credit_amount' => $amount,
                'rest_balance' => $oldWallet->balance + $amount,
                'txn_date' => now(),
                'txn_for' => $log ?? 'Add Money into Wallet',
                'txn_method' => $method ?? 'Wallet Transaction',
                'txn_byuser_id' => Auth::guard('admin')->id(),
                'comment' => $log,
                'created_at' => now(),
            ];
            $oldWallet->update($Wallet_update);
            $TxnResult = WalletTxn::create($Wallet_Transaction);
            staffLog('wallet', $oldWallet->id, 'update', "User wallet money updated");
            staffLog('wallet_txn', $TxnResult->id, 'create', 'User wallet txn created, added money: ' . $amount . ' and new balance: ' . ($amount + $oldWallet->balance));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}

// ---------------------------------------------------
if (!function_exists('SendSMS')) {
    function SendSMS($phone)
    {
        $smsApiConfig = GlobalSetting('sms_api');
        $randString = rand(1, 999999);
        $otpValue = str_pad($randString, 6, '0', STR_PAD_LEFT);
        $otpEntryValues = [
            'phone' => $phone,
            'otp' => $otpValue,
            'valid_for' => '2',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($smsApiConfig == 'fast2sms') {
            $result = Fast2SMS($phone, $otpEntryValues);
        } elseif ($smsApiConfig == 'nicesms') {
            $templateId = env('NICE_OTP_TEMP');
            $result = NiceSMS($otpEntryValues, $templateId);
        } else {
            return false;
        }
        if ($result['status'] == 1) {
            DB::table('otp_entries')->insertGetId($otpEntryValues);

            return $result;
        }

        return $result;
    }
}

// ----------------------------------------------------------
// ---------------------------------------------------
if (!function_exists('SendOrderConfirmSMS')) {
    function SendOrderConfirmSMS($for, $orderNo)
    {
        $smsApiConfig = GlobalSetting('sms_api');
        // -------------------------------------------
        $masterOrder = MasterOrder::where('order_no', $orderNo)->first();
        $originalURL = env('WEB_URL') . '/view-order/' . encryptAnyId($orderNo);
        $shortCode = substr(md5(uniqid($originalURL, true)), 0, 6);
        $shortUrl = ShortUrl::create([
            'original_url' => $originalURL,
            'url' => $shortCode,
        ]);
        if ($smsApiConfig == 'fast2sms') {
            $msgString = 'https://amitbookdepot.com/AMITBO/srt-url/' . $shortUrl->url;
            $smsArray = [
                'phone' => $masterOrder->phone,
                'otp' => $masterOrder->id,
                'otp_from' => 'erp_order_completion',
                'valid_for' => '200',
                'user_request' => $msgString,
            ];
            // -------------------------------------------
            $templateId = '171081';
            $smsArray['token'] = $templateId;
            $result = Fast2SMS($masterOrder->phone, $smsArray);
        } elseif ($smsApiConfig == 'nicesms') {
            $msgString = 'Thank%20you%20for%20visiting%20AMIT%20BOOK%20DEPOT.%20Click%20below%20to%20download%20e-invoice%20.%20https%3A%2F%2Famitbookdepot.com/AMITBO/srt-url/' . urlencode($shortUrl->url);
            $smsArray = [
                'phone' => $masterOrder->phone,
                'otp' => $masterOrder->id,
                'otp_from' => 'erp_order_completion',
                'valid_for' => '200',
                'user_request' => $msgString,
            ];
            $templateId = '1107162549276982184';
            $smsArray['token'] = $templateId;
            $result = NiceSMS($smsArray, $templateId);
        } else {
            return false;
        }
        if ($result['status'] == 1) {
            OtpEntries::create($smsArray);
            return true;
        }
        return true;
    }
}
// ----------------------------------------------------------

if (!function_exists('Fast2SMS')) {
    function Fast2SMS($phone, $otpEntryValues)
    {
        if (isset($otpEntryValues['otp_from']) && $otpEntryValues['otp_from'] == 'erp_order_completion') {
            $url = env('FAST2SMS_URL');
            $templateId = $otpEntryValues['token'];
            $postData = [
                'sender_id' => env('FAST2SMS_SENDER_ID'),
                'message' => $templateId,
                'variables_values' => $otpEntryValues['user_request'] . '|',
                'route' => 'dlt',
                'numbers' => $phone,
            ];
        } else {
            $url = env('FAST2SMS_URL');
            $templateId = env('FAST2SMS_LOGIN_TEMP');
            $postData = [
                'sender_id' => env('FAST2SMS_SENDER_ID'),
                'message' => $templateId,
                'variables_values' => $otpEntryValues['otp'] . '|',
                'route' => 'dlt',
                'numbers' => $phone,
            ];
        }


        $MetaData = [
            'authorization' => 'gpeMj1D0sGZ68tHKESI3c7rkdBxahoiAYUfVunlJOzvXwLFQ5mNb0km6MytD98XHIrFqn5iYaQzVsTU4',
            'accept' => '*/*',
            'cache-control' => 'no-cache',
            'content-type' => 'application/json',
        ];
        $APIData = Http::withHeaders($MetaData)->post($url, $postData);
        $responce = json_decode($APIData);
        if ($responce->return === true) {
            return [
                'status' => 1,
                'message' => 'SMS sent successfully.',
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'SMS sending failed.',
            ];
        }
    }
}

//highlights the selected navigation on admin panel
if (!function_exists('NiceSMS')) {
    function NiceSMS($otpEntryValues, $templateId)
    {
        $user = env('NICE_SMS_USER');
        $sid = env('NICE_SMS_SID');
        $api_url = env('NICE_SMS_URL');
        $signature = env('NICE_SMS_SIGNATURE');
        $entityid = env('NICE_SMS_ENTITYID');
        $phone = $otpEntryValues['phone'];
        $otp = $otpEntryValues['otp'];
        if (!$user || !$sid || !$api_url) {
            return [
                'status' => 0,
                'message' => 'SMS sending failed due to missing environment variables.',
            ];
        }
        try {
            if (isset($otpEntryValues['otp_from']) && $otpEntryValues['otp_from'] == 'erp_order_completion') {
                $smsString = $otpEntryValues['user_request'];
            } else {
                $smsString = 'OTP%20is%20-%20' . $otp . '%20is%20your%20AMIT%20BOOK%20DEPOT%20verification%20Code';
            }
            $url = "$api_url?username=$user&dest=$phone&apikey=$sid&signature=$signature&msgtype=PM&msgtxt=$smsString&templateid=$templateId&entityid=$entityid";
            $client = new Client;
            $response = $client->post($url);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $responseBody = json_decode($response->getBody(), true);
                if ($responseBody[0]['code'] == '6001') {
                    return [
                        'status' => 1,
                        'message' => $responseBody[0]['desc'],
                    ];
                } else {
                    return [
                        'status' => 0,
                        'message' => 'SMS sending failed with response code: ' . $responseBody[0]['code'],
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'message' => "SMS sending failed with HTTP status code: $statusCode",
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => 'Exception while sending SMS: ' . $e->getMessage(),
            ];
        }
    }
}

//highlights the selected navigation on admin panel
if (!function_exists('FlashBanner')) {
    function FlashBanner($place)
    {
        $flash = DB::table('flash')->where('place', $place)->where('status', 'active')->first();
        if ($flash) {
            return $flash;
        } else {
            return false;
        }
    }
}

// ---------------------------------------
if (!function_exists('CreateSupplierWallet')) {
    function CreateSupplierWallet($supplier_id)
    {
        $Wallet_data = [
            'supplier_id' => $supplier_id,
            'balance' => 0.00,
            'last_txn_id' => null,
            'last_txn_amount' => 0.00,
            'last_txn_by_id' => Auth::guard('admin')->id(),
            'last_txn_at' => now(),
        ];
        $walletResult = SupplierWallet::create($Wallet_data);
        if ($walletResult) {
            staffLog('supplier_wallet', $walletResult->id, 'create', ' supplier wallet created');
            return true;
        } else {
            return false;
        }
    }
}

// ------------------------------Credit Money into Wallet
if (!function_exists('DebitSupplierTxn')) {
    function DebitSupplierTxn($suppliedId, $dataBase)
    {

        try {
            DB::beginTransaction();
            $supplier = Supplier::find($suppliedId);
            $oldBalancia = SupplierWallet::where('supplier_id', $supplier->id)->first();
            $sWUpdate = [
                'balance' => $oldBalancia->balance - $dataBase['amount'],
                'last_txn_amount' => $dataBase['amount'],
                'last_txn_by_id' => Auth::guard('admin')->id(),
                'last_txn_at' => now(),
            ];
            $SWtxnCreate = [
                'wallet_id' => $oldBalancia->id,
                'supplier_id' => $supplier->id,
                'debit' => $dataBase['amount'],
                'credit' => 0.00,
                'invoice_id' => $dataBase['invoice_id'] ?? null,
                'debit_account' => $dataBase['debit_account'],
                'payment_method' => $dataBase['payment_method'],
                'bank_charges' => $dataBase['bank_charges'],
                'clearance_date' => $dataBase['clearance_date'],
                'initiation_date' => $dataBase['initiation_date'],
                'for' => $dataBase['for'],
                'log' => $dataBase['log'],
                'comment' => $dataBase['comment'],
                'txn_id' => $dataBase['txn_id'],
                'txn_byuser_id' => Auth::guard('admin')->id(),
            ];
            $TxnResult = SupplierTxn::create($SWtxnCreate);
            $sWUpdate['last_txn_id'] = $TxnResult->id;
            $oldBalancia->update($sWUpdate);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet transaction failed: ' . $e->getMessage(), [
                'supplierId' => $suppliedId,
                'amount' => $dataBase['amount'],
                'exception' => $e,
            ]);
            return false;
        }
    }
}

// ---------------------------------- Debit Money into Wallet
if (!function_exists('CreditSupplierTxn')) {
    function CreditSupplierTxn($suppliedId, $dataBase)
    {
        try {
            DB::beginTransaction();
            $supplier = Supplier::find($suppliedId);
            $oldBalancia = SupplierWallet::where('supplier_id', $supplier->id)->first();
            $sWUpdate = [
                'balance' => $oldBalancia->balance + $dataBase['amount'],
                'last_txn_amount' => $dataBase['amount'],
                'last_txn_by_id' => Auth::guard('admin')->id(),
                'last_txn_at' => now(),
            ];
            $SWtxnCreate = [
                'wallet_id' => $oldBalancia->id,
                'supplier_id' => $supplier->id,
                'debit' => 0.00,
                'credit' => $dataBase['amount'],
                'invoice_id' => $dataBase['invoice_id'],
                'debit_account' => $dataBase['debit_account'],
                'payment_method' => $dataBase['payment_method'],
                'bank_charges' => $dataBase['bank_charges'],
                'clearance_date' => $dataBase['clearance_date'],
                'initiation_date' => $dataBase['initiation_date'],
                'for' => $dataBase['for'],
                'log' => $dataBase['log'],
                'comment' => $dataBase['comment'],
                'txn_id' => $dataBase['txn_id'],
                'txn_byuser_id' => Auth::guard('admin')->id(),
            ];
            $TxnResult = SupplierTxn::create($SWtxnCreate);
            $sWUpdate['last_txn_id'] = $TxnResult->id;
            $oldBalancia->update($sWUpdate);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet transaction failed: ' . $e->getMessage(), [
                'supplierId' => $suppliedId,
                'amount' => $dataBase['amount'],
                'exception' => $e,
            ]);
            return false;
        }
    }
}

if (! function_exists('sendNotify')) {
    function sendNotify($userID, $for, $type, $message, $priority, $visible, $target)
    {
        // $notify = Notification::create([
        //     'user_id' => $userID, // user ID / null for all users
        //     'for' => $for, // for as title of notification
        //     'type' => $type ?? 'info', // its type of alert / notification 'info', 'warning', 'error'
        //     'message' => $message, // its contain all message
        //     'priority' => $priority ?? 'medium', // its will set the staff priority to take action of it  'low', 'medium', 'high'
        //     'visible_to' => $visible, // enum = user, admin, both
        //     'staff_id' => Auth::guard('admin')->id(),
        //     'target_id' => $target ?? null, // this will insure that each notification will have a target to check the details
        // ]);
        return true;
    }
}

if (! function_exists('CreateShippingInvoice')) {
    function CreateShippingInvoice($masterOrder)
    {
        $lastShipping = ShippingInvoice::latest()->value('id') ?? 0;
        $shippingInvoiceNumber = 'ASI' . str_pad($lastShipping + 1, 4, '0', STR_PAD_LEFT);
        ShippingInvoice::create([
            'invoice_id' => $shippingInvoiceNumber,
            'zone_id' => $masterOrder->shipping_zone_id,
            'shipping_method' => $masterOrder->shipping_type,
            'shipping_id' => $masterOrder->shipping_method_id,
            'pickup_id' => $masterOrder->pickup_id,
            'shipping_charges' => $masterOrder->shipping_charges,
            'user_id' => $masterOrder->user_id,
            'order_id' => $masterOrder->id,
            'order_no' => $masterOrder->order_no,
        ]);
    }
}

if (! function_exists('encryptAnyId')) {
    function encryptAnyId($data)
    {
        $secretKey = 'amit-book-depot-9216499664';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $secretKey, 0, $iv);
        return base64_encode($encryptedData . '::' . $iv);
    }
}


if (! function_exists('decryptAnyId')) {
    function decryptAnyId($encryptedData)
    {
        $secretKey = 'amit-book-depot-9216499664';
        list($encryptedData, $iv) = explode('::', base64_decode($encryptedData), 2);
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $secretKey, 0, $iv);
    }
}

// app/Helpers/helpers.php
if (! function_exists('add_query_params')) {
    function add_query_params($url, $request) {
        // Parse the current URL to extract existing query parameters
        $urlParts = parse_url($url);
        parse_str($urlParts['query'] ?? '', $existingParams);

        // Add all request parameters except 'page' (for pagination)
        foreach ($request->all() as $key => $value) {
            // Skip 'page' because it's handled automatically by Laravel pagination
            if ($key !== 'page') {
                $existingParams[$key] = $value;
            }
        }

        // Rebuild the URL with all the parameters
        $newQuery = http_build_query($existingParams);
        return $urlParts['path'] . '?' . $newQuery;
    }
}
if (! function_exists('organization_name')) {
    function organization_name() {
        // Find the organization by the current authenticated user's organization_id
        $Organization = Organization::find(Auth::guard('admin')->user()->organization_id);

        // Check if the organization exists, and return the name, otherwise return a default name
        if ($Organization) {
            return $Organization->name;
        } else {
            return "Amit Book D. <sup>0.1</sup>";
        }
    }
}

