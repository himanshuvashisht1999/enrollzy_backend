<?php

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

Artisan::command('permissions:setup', function () {
    // Define all permissions
    $allPermissions = [
        'category-browse',
        'category-read',
        'category-edit',
        'category-add',
        'category-delete',
        // -----------------
        'subcategory-browse',
        'subcategory-read',
        'subcategory-edit',
        'subcategory-add',
        'subcategory-delete',
        // -----------------
        'sub-subcategory-browse',
        'sub-subcategory-read',
        'sub-subcategory-edit',
        'sub-subcategory-add',
        'sub-subcategory-delete',
        // -----------------
        'author-browse',
        'author-read',
        'author-edit',
        'author-add',
        'author-delete',
        // -----------------
        'author-category-browse',
        'author-category-read',
        'author-category-edit',
        'author-category-add',
        'author-category-delete',
        // -----------------
        'options-browse',
        'options-read',
        'options-edit',
        'options-add',
        'options-delete',
        // -----------------
        'option-values-browse',
        'option-values-read',
        'option-values-edit',
        'option-values-add',
        'option-values-delete',
        // -----------------
        'brand-browse',
        'brand-read',
        'brand-edit',
        'brand-add',
        'brand-delete',
        // -----------------
        'publisher-browse',
        'publisher-read',
        'publisher-edit',
        'publisher-add',
        'publisher-delete',
        // -----------------
        'product-suppliers-browse',
        'product-suppliers-read',
        'product-suppliers-edit',
        'product-suppliers-add',
        'product-suppliers-delete',
        // -----------------
        'product-browse',
        'product-read',
        'product-edit',
        'product-add',
        'product-delete',
        // -----------------
        'lowstock-product-browse',
        'lowstock-product-edit',
        // -----------------
        'inhouse-orders-browse',
        'inhouse-orders-read',
        'inhouse-orders-edit',
        // -----------------
        'refund-requested-browse',
        'refund-requested-read',
        'refund-requested-edit',
        // -----------------
        'create-refund-browse',
        'create-refund-read',
        'create-refund-edit',
        // -----------------
        'prebooking-browse',
        'prebooking-read',
        'prebooking-edit',
        'prebooking-add',
        // -----------------
        'sales-order-browse',
        'sales-order-read',
        'sales-order-edit',
        'sales-order-add',
        // -----------------
        'supplier-ap-browse',
        'supplier-ap-read',
        'supplier-ap-edit',
        'supplier-ap-add',
        // -----------------
        'old-ap-browse',
        'old-ap-read',
        'old-ap-edit',
        'old-ap-add',
        // -----------------
        'old-apX2-browse',
        'old-apX2-read',
        'old-apX2-edit',
        'old-apX2-add',
        // -----------------
        'expense-browse',
        'expense-read',
        'expense-edit',
        'expense-add',
        // -----------------
        'rent-return-browse',
        'rent-return-read',
        'rent-return-edit',
        'rent-return-add',
        // -----------------
        'invoice-browse',
        'invoice-read',
        // -----------------
        'supplier-statement-browse',
        'supplier-statement-read',
        'supplier-statement-edit',
        'supplier-statement-add',
        // -----------------
        'customer-browse',
        'customer-read',
        'customer-edit',
        'customer-add',
        // -----------------
        'customer-category-browse',
        'customer-category-read',
        'customer-category-edit',
        'customer-category-add',
        'customer-category-delete',
        // -----------------
        'institute-browse',
        'institute-read',
        'institute-edit',
        'institute-add',
        'institute-delete',
        // -----------------
        'seller-request-browse',
        'seller-request-read',
        'seller-request-edit',
        // -----------------
        'seller-browse',
        'seller-read',
        'seller-edit',
        'seller-add',
        'seller-delete',
        // -----------------
        'wallet-browse',
        'wallet-read',
        'wallet-edit',
        // -----------------
        'wallet-balance-browse',
        'wallet-balance-read',
        'wallet-balance-edit',
        'wallet-balance-add',
        // -----------------
        'blog-category-browse',
        'blog-category-read',
        'blog-category-edit',
        'blog-category-add',
        'blog-category-delete',
        // -----------------
        'blog-author-browse',
        'blog-author-read',
        'blog-author-edit',
        'blog-author-add',
        'blog-author-delete',
        // -----------------
        'blog-post-browse',
        'blog-post-read',
        'blog-post-edit',
        'blog-post-add',
        'blog-post-delete',
        // -----------------
        'blog-comment-browse',
        'blog-comment-read',
        'blog-comment-edit',
        'blog-comment-add',
        'blog-comment-delete',
        // -----------------
        'short-url-browse',
        'short-url-read',
        'short-url-edit',
        'short-url-add',
        'short-url-delete',
        // -----------------
        'seo-plugin-browse',
        'seo-plugin-read',
        'seo-plugin-edit',
        // -----------------
        'slide-banner-browse',
        'slide-banner-read',
        'slide-banner-edit',
        'slide-banner-add',
        'slide-banner-delete',
        // -----------------
        'flash-banner-browse',
        'flash-banner-read',
        'flash-banner-edit',
        'flash-banner-add',
        'flash-banner-delete',
        // -----------------
        'social-media_icon-browse',
        'social-media_icon-read',
        'social-media_icon-edit',
        'social-media_icon-add',
        'social-media_icon-delete',
        // -----------------
        'page-list-browse',
        'page-list-read',
        'page-list-edit',
        'page-list-add',
        'page-list-delete',
        // -----------------
        'coupons-browse',
        'coupons-read',
        'coupons-edit',
        'coupons-add',
        'coupons-delete',
        // -----------------
        'shipping-zone-browse',
        'shipping-zone-read',
        'shipping-zone-edit',
        'shipping-zone-add',
        'shipping-zone-delete',
        // -----------------
        'shipping-method-browse',
        'shipping-method-read',
        'shipping-method-edit',
        'shipping-method-add',
        'shipping-method-delete',
        // -----------------
        'local-pickup_point-browse',
        'local-pickup_point-read',
        'local-pickup_point-edit',
        'local-pickup_point-add',
        'local-pickup_point-delete',
        // -----------------
        'courier-browse',
        'courier-read',
        'courier-edit',
        'courier-add',
        'courier-delete',
        // -----------------
        'general-setting-browse',
        'general-setting-read',
        'general-setting-edit',
        // -----------------
        'department-browse',
        'department-read',
        'department-edit',
        'department-add',
        'department-delete',
        // -----------------
        'designation-browse',
        'designation-read',
        'designation-edit',
        'designation-add',
        'designation-delete',
        // -----------------
        'staff-browse',
        'staff-read',
        'staff-edit',
        'staff-add',
        'staff-delete',
        // -----------------
        'roles-browse',
        'roles-read',
        'roles-edit',
        'roles-add',
        'roles-delete',
        // -----------------
    ];
    // Clear cache to avoid permission cache issues
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    // Delete all related records in model_has_permissions and model_has_roles
    DB::table('model_has_permissions')->delete();
    DB::table('model_has_roles')->delete();
    DB::table('role_has_permissions')->delete();
    // Delete all permissions and roles
    Permission::query()->delete();
    Role::query()->delete();
    // Reset auto-increment values
    DB::statement('ALTER TABLE permissions AUTO_INCREMENT = 1');
    DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1');
    // Recreate all permissions
    foreach ($allPermissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'admin']);
    }
    // Create superadmin role and assign all permissions
    $superadminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
    $superadminRole->syncPermissions(Permission::all());
    // Assign the 'superadmin' role to the user with ID 73 (example user)
    $user = Admin::find(1); // Replace with the actual user ID
    if ($user) {
        $user->assignRole('superadmin');
        $this->info('User assigned the superadmin role successfully.');
    } else {
        $this->error('User not found.');
    }
    $this->info('Roles and permissions have been set up successfully.');
})->describe('Setup roles and permissions for the application');
