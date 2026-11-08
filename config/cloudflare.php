<?php

/*
 * Cloudflare's IP ranges (https://www.cloudflare.com/ips/, checked 28 Oct 2026). The live site runs behind Cloudflare:
 * only requests from these addresses may tell Laravel the visitor's real IP (X-Forwarded-For), so $request->ip() is the
 * visitor and not Cloudflare. Anyone else sending that header is ignored. Cloudflare changes the list rarely: check it
 * once in a while, or the visitors behind a new range all look like one IP (and share its OTP log and IP block).
 */
return [
    'ips' => [
        // IPv4
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
        '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ],
];
