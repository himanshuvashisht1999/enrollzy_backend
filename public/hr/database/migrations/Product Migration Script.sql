-- dummy user for admin table
INSERT INTO `admin` (`id`, `username`, `google_id`, `password`, `name`, `email`, `phone`, `profile_image`, `role`, `status`, `gender`, `dob`, `address`, `about`, `joining_date`, `pay_based`, `salary`, `working_days`, `department_id`, `designation_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (NULL, 'vishal', NULL, '$2y$12$9Rz6tCdoxN/mp2bQBAgLhe9PsUQQg/mFrHJxcyxyGZmBP2viycLZ6', 'Vishal', 'vishal@gmail.com', '8360840040', NULL, 'superadmin', 'active', 'male', '1999-08-13', 'Chandiagrh', NULL, NULL, '', '', NULL, NULL, NULL, current_timestamp(), current_timestamp(), NULL);


-- new query for drop and changes admin data
ALTER TABLE `category` CHANGE `user_id` `admin_id` INT(20) NOT NULL;
ALTER TABLE `category` DROP `user_role`;


-- dummy user for admin table




INSERT INTO
    products (
        id,
        name,
        slug,
        seller_id,
        user_role,
        user_id,
        category_id,
        sub_category_id,
        sub_subcategory_id,
        publisher_id,
        author_id,
        description,
        refundable,
        refund_limit,
        product_type,
        on_rent,
        rent_amount,
        security_amount,
        rent_return,
        version,
        isbn,
        old_isbn,
        tags,
        origin,
        top_product,
        featured_product,
        gst,
        mrp
    )
SELECT
    id,
    name,
    slug,
    user_id as seller_id,
    added_by as user_role,
    user_id,
    category_id,
    subcategory_id as sub_category_id,
    subsubcategory_id as sub_subcategory_id,
    brand_id as publisher_id,
    author_id,
    description,
    'yes' as refundable,
    '3' as refund_limit,
    'book' as product_type,
    onrent as on_rent,
    rentamount as rent_amount,
    securityamount as security_amount,
    'semester_return' as rent_return,
    version,
    isbn,
    oldisbn as old_isbn,
    tags,
    origin,
    '1' as top_product,
    '1' as featured_product,
    tax as gst,
    mrp
FROM
    gudgudram_products
WHERE
    added_by = 'admin' ON DUPLICATE KEY
UPDATE
    id =
VALUES
    (id),
    name =
VALUES
    (name),
    slug =
VALUES
    (slug),
    seller_id =
VALUES
    (seller_id),
    user_role =
VALUES
    (user_role),
    user_id =
VALUES
    (user_id),
    category_id =
VALUES
    (category_id),
    sub_category_id =
VALUES
    (sub_category_id),
    sub_subcategory_id =
VALUES
    (sub_subcategory_id),
    publisher_id =
VALUES
    (publisher_id),
    author_id =
VALUES
    (author_id),
    description =
VALUES
    (description),
    refundable =
VALUES
    (refundable),
    refund_limit =
VALUES
    (refund_limit),
    product_type =
VALUES
    (product_type),
    on_rent =
VALUES
    (on_rent),
    rent_amount =
VALUES
    (rent_amount),
    security_amount =
VALUES
    (security_amount),
    rent_return =
VALUES
    (rent_return),
    version =
VALUES
    (version),
    isbn =
VALUES
    (isbn),
    old_isbn =
VALUES
    (old_isbn),
    tags =
VALUES
    (tags),
    origin =
VALUES
    (origin),
    top_product =
VALUES
    (top_product),
    featured_product =
VALUES
    (featured_product),
    gst =
VALUES
    (gst),
    mrp =
VALUES
    (mrp);

-----------------------------------------------------------------------------------
-- update has variaion filed based on variant_product in gudgudram db and Products table
UPDATE
    products p
    JOIN gudgudram_products g ON p.id = g.id
SET
    p.status = CASE
        WHEN g.published = '1' THEN 'active'
        WHEN g.published = '0' THEN 'inactive'
        ELSE p.status
    END
WHERE
    g.added_by = 'admin';

-- update Any field based on same as above
UPDATE
    products p
    JOIN gudgudram_products g ON p.id = g.id
SET
    p.mrp = '0',
    p.gst = '0',
    p.low_stock = '0',
    p.low_stock = '0'
WHERE
    g.added_by = 'admin';

--  =============================================================================
UPDATE
    products p
    JOIN live_products l ON p.id = l.id
SET
    p.seller_id = '5',
    p.discount_type = l.discount_type,
    p.min_order = l.minorderqty,
    p.max_order = l.maxorderqty,
    p.stock = l.current_stock,
    p.low_stock = l.low_stock,
WHERE
    g.added_by = 'admin';

-- Insert into variant_options table for 'Old' version variants
INSERT INTO
    variant_options (
        variant_id,
        option_id,
        value_id,
        created_at,
        updated_at
    )
SELECT
    pv.id AS variant_id,
    o.id AS option_id,
    ov.id AS value_id,
    NOW() AS created_at,
    NOW() AS updated_at
FROM
    variants pv
    JOIN `options` o ON o.name = 'Purchase Type' -- Ensure this matches the actual option name
    JOIN option_values ov ON ov.option_id = o.id
    AND ov.value = 'Old'
WHERE
    pv.version = 'old';

-- Filter for old version variants
-- Mapping 'New' value
-----------------------------------------------------------------------
-- Switch to the correct database
USE amitbookdepot;

-- Insert 'Old' variants
INSERT INTO
    variants (
        product_id,
        name,
        combo,
        gst,
        mrp,
        low_stock,
        min_order,
        max_order,
        isbn,
        version,
        old_isbn,
        on_rent,
        rent_amount,
        security_amount,
        rent_return,
        refundable,
        refund_limit,
        status,
        tags,
        `default` -- Use backticks to avoid issues with the reserved keyword
    )
SELECT
    p.id AS product_id,
    CONCAT(p.name, ' - New') AS name,
    'New' AS combo,
    p.gst,
    p.mrp,
    p.low_stock,
    p.min_order,
    p.max_order,
    p.isbn,
    'new' AS version,
    p.old_isbn,
    p.on_rent,
    p.rent_amount,
    p.security_amount,
    p.rent_return,
    p.refundable,
    p.refund_limit,
    p.status,
    p.tags,
    'no' AS `default` -- Use backticks to avoid issues with the reserved keyword
FROM
    Products p
WHERE
    p.has_variation = 'yes';

-- ===================================================================================================
UPDATE
    variants v
    JOIN live_product_stocks ps ON v.isbn = ps.isbn
    AND v.product_id = ps.product_id
SET
    v.stock = ps.qty,
    v.mrp = ps.mrp,
    v.sell_web = ps.price,
    v.sell_erp = ps.erpprice,
    v.sell_app = ps.price,
    v.rent_amount = ps.rent_amount,
    v.security_amount = ps.rent_security;

-- == == == == == == == == == == == == == == == == == == == == == == == == -- सही डेटाबेस में स्विच करें
UPDATE
    products v
    JOIN (
        SELECT
            id,
            mrp,
            gst,
            sell_web,
            sell_app,
            sell_erp,
            -- डिस्काउंट की गणना प्रतिशत में
            100 - ((sell_erp / mrp) * 100) AS discount_erp,
            100 - ((sell_web / mrp) * 100) AS discount_web,
            100 - ((sell_app / mrp) * 100) AS discount_app,
            -- बेस प्राइस की गणना GST के साथ
            sell_erp / (1 + (gst / 100)) AS base_erp,
            sell_web / (1 + (gst / 100)) AS base_web,
            sell_app / (1 + (gst / 100)) AS base_app
        FROM
            products
        WHERE
            seller_id = '7'
    ) calc ON v.id = calc.id
SET
    v.discount_erp = calc.discount_erp,
    v.discount_web = calc.discount_web,
    v.discount_app = calc.discount_app,
    v.base_erp = calc.base_erp,
    v.base_web = calc.base_web,
    v.base_app = calc.base_app;

-- ============================================================================
UPDATE
    products
SET
    isbn = NULL,
    old_isbn = NULL,
    mrp = NULL,
    refundable = null,
    refund_limit = null,
    on_rent = null,
    rent_amount = null,
    security_amount = null,
    rent_return = null,
    version = null,
    isbn = null,
    old_isbn = null,
WHERE
    has_variation = 'yes';

-- ================================================================
UPDATE
    products p
    JOIN gudgudram_products gp ON p.id = gp.id
SET
    p.gst = gp.tax,
    p.mrp = gp.mrp,
    p.sell_app = gp.selling_price,
    p.sell_web = gp.selling_price,
    p.sell_erp = gp.erpprice,
WHERE
    p.id = gp.id
    AND p.has_variation = 'no';

-- ==================================================
ALTER TABLE
    wallet AUTO_INCREMENT = 1;

-- ====================================================================
-- सबसे पहले `product_images` टेबल में रिकॉर्ड्स को INSERT करें
INSERT INTO
    product_image (product_id, featured, flash, gallery)
SELECT
    p.id AS product_id,
    gp.photos AS featured,
    gp.photos AS flash,
    gp.photos AS gallery,
    NOW() AS created_at,
    NOW() AS updated_at
FROM
    products p
    JOIN gudgudram_products gp ON p.id = gp.id
WHERE
    gp.photos IS NOT NULL;

-- ========================================================================
INSERT INTO
    product_images (variant_id, featured, flash, gallery)
SELECT
    v.id AS variant_id,
    gp.featured_img AS featured,
    gp.featured_img AS flash,
    gp.featured_img AS gallery
FROM
    variants v
    JOIN gudgudram_products gp ON v.product_id = gp.id
WHERE
    gp.featured_img IS NOT NULL;

-- ==================================================================================
UPDATE
    product_image
SET
    gallery = REPLACE(gallery,'/uploads/products/photos/','/photos/product/')
WHERE
    gallery LIKE '/uploads/products/photos/%';

-- ======================================================================================
INSERT INTO
    wallet (user_id, balance, status)
SELECT
    id,
    0,
    'active'
FROM
    users;

-- -=======================================================
UPDATE
    variaion v
    JOIN live_products lp ON v.id = lp.id
SET
    v.gst = lp.tax,
    v.min_order = lp.minorderqty
WHERE
    p.product_id = lp.id;

--------------------
UPDATE
    products
SET
    sub_subcategory_id = REPLACE(
        REPLACE(REPLACE(sub_subcategory_id, '["', ''), '"]', ''),
        '"',
        ''
    );

-- =====================================================
INSERT INTO
    users (
        id,
        username,
        password,
        name,
        email,
        email_verified_at,
        phone,
        profile_image,
        role,
        status,
        gender,
        dob,
        state,
        city,
        address,
        landmark,
        pincode,
        legal_name,
        valid_doctype,
        valid_docimage,
        registration_at,
        approver_id,
        approvedby,
        approve_at,
        about,
        father_name,
        tehsil,
        categoryid,
        instituteid,
        district,
        user_type,
        gstin,
        invoice_prefix,
        created_at,
        updated_at
    )
SELECT
    id,
    NULL AS username,
    -- Assuming there's no corresponding field in old_users
    password,
    name,
    email,
    email_verified_at,
    phone,
    avatar AS profile_image,
    -- Mapping old avatar to new profile_image
    NULL AS role,
    -- Assuming there's no corresponding field in old_users
    is_block AS status,
    -- Mapping old is_block to new status (1-active, 0-block)
    NULL AS gender,
    -- Assuming there's no corresponding field in old_users
    NULL AS dob,
    -- Assuming there's no corresponding field in old_users
    state,
    city,
    address,
    landmark,
    postal_code AS pincode,
    -- Mapping old postal_code to new pincode
    NULL AS legal_name,
    -- Assuming there's no corresponding field in old_users
    NULL AS valid_doctype,
    -- Assuming there's no corresponding field in old_users
    NULL AS valid_docimage,
    -- Assuming there's no corresponding field in old_users
    register_on AS registration_at,
    NULL AS approver_id,
    -- Assuming there's no corresponding field in old_users
    NULL AS approvedby,
    -- Assuming there's no corresponding field in old_users
    NULL AS approve_at,
    -- Assuming there's no corresponding field in old_users
    NULL AS about,
    -- Assuming there's no corresponding field in old_users
    father_name,
    tehsil,
    category_id AS categoryid,
    institute_id AS instituteid,
    district,
    user_type,
    gstin,
    NULL AS invoice_prefix,
    -- Assuming there's no corresponding field in old_users
    created_at,
    updated_at
FROM
    live_users;


------------------Seller Product migration -------------------------------------------------------------------------------------------------------------/


INSERT INTO product (
    id,
    name,
    slug,
    seller_id,
    user_role,
    user_id,
    category_id,
    sub_category_id,
    sub_subcategory_id,
    brand_id,
    author_id,
    description,
    refundable,
    refund_limit
    product_type,
    on_rent,
    rent_amount,
    security_amount,
    version,
    isbn,
    old_isbn,
    origin,
    top_product,
    featured_product,
    has_variation,
    weight,
    gst,
    mrp,
    discount_type,
    sell_web,
    sell_app,
    sell_erp,
    min_order,
    max_order,
    stock,
    low_stock,
    status
)
SELECT
    id,
    name,                            -- Mapped directly
    slug,                            -- Mapped directly
    '7' AS seller_id,           -- Assuming a fixed user role
    'admin' AS user_role,           -- Assuming `added_by` is equivalent to `seller_id`
    '28' AS user_id,                         -- Mapped directly
    category_id,                     -- Mapped directly
    subcategory_id AS sub_category_id, -- Mapping `subcategory_id` to `sub_category_id`
    subsubcategory_id AS sub_subcategory_id, -- Mapping `subsubcategory_id` to `sub_subcategory_id`
    brand_id,                        -- Mapped directly
    author_id,                       -- Mapped directly
    description,                     -- Mapped directly
    CASE WHEN refundable = 1 THEN 'yes' ELSE 'no' END AS refundable,  -- Logic for refundable
    CASE WHEN refundable = 1 THEN 3 ELSE 0 END AS refund_limit,       -- Logic for refund_limit
    'stationary' AS product_type,      -- Assuming a default value for product_type
    onrent AS on_rent,               -- Mapped directly
    rentamount AS rent_amount,       -- Mapped directly
    securityamount AS security_amount, -- Mapped directly
    version,                         -- Mapped directly
    isbn,                            -- Mapped directly
    oldisbn AS old_isbn,             -- Mapped directly
    origin,                          -- Mapped directly
    CASE WHEN top_product = 1 THEN 'yes' ELSE 'no' END AS top_product,  -- Logic for top product
    CASE WHEN featured = 1 THEN 'yes' ELSE 'no' END AS featured_product,  -- Logic for featured product
    'no' AS has_variation, -- Assuming `variant_product` indicates if there are variations
    weight,                          -- Mapped directly
    tax AS gst,       -- Summing up GST components
    mrp,                             -- Mapped directly
    discount_type,                   -- Mapped directly
    selling_price AS sell_web,       -- Mapped directly from `selling_price`
    selling_price AS sell_app,       -- Assuming the same as `sell_web`
    erpprice AS sell_erp,       -- Assuming the same as `sell_web`
    minorderqty AS min_order,        -- Mapped directly
    maxorderqty AS max_order,        -- Mapped directly
    current_stock AS stock,          -- Mapped directly
    minstock AS low_stock,           -- Mapped directly
    CASE WHEN published = 1 THEN 'active' ELSE 'inactive' END AS status,  -- Logic for top product
FROM seller_product;

------------------------------------ Publisher to Brnad migration

INSERT INTO brand (
    id,
    name,
    slug,
    photo,
    description,
    user_role,
    user_id,
    status,
    created_at,
    updated_at,
    deleted_at
)
SELECT
    id,
    name,
    slug,
    logo AS photo,
    description,
    user_role,
    user_id,
    status,
    created_at,
    updated_at,
    deleted_at
FROM publisher
WHERE id IN (
    SELECT brand_id FROM product WHERE seller_id = 7
);
