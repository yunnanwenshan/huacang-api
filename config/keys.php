<?php
/**
 * app 端验证
 */
return [
    /*所需要的公钥
     * 该key为App-Token
     */
    'public_keys_for_verify_signature' => [

        'ios' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7yX3x7ZBh5YU+okd/O+rb9koN
k4480+UcDe9VODDak2BP3TCzVR0rXAnjSbcVGurBhqFiLooewbQFcQulfJcjghSY
U3xEMcvqWnXPE1JJFoUKEx5IJwNtiRJ90rtZ8P5FN2NcYmnBK2h11X3t3ek9BOSi
bJLZzod7es9xmb9Q7QIDAQAB
-----END PUBLIC KEY-----
',
        'android' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7U3Ytojq+URXsjJoE0nIKM+br
xMvMJTe4zP1nbmRPS7IJZOTkSKAz551NLrUr5O6mIbA4ww9j1VDKQlWSJ+5i+mA7
YcAgvcq6hN0cUQPh11TClNMjlhShmflZkDggude/rPWUNs5AV/eZ6wenzXZANS1G
OH12xbUnMzIYGrExYQIDAQAB
-----END PUBLIC KEY-----
',
        'web' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDaGGFJjSZkFDZ76Pt/jQwhVBMf
gYj7eKRx/uXbA0uxDBjMoVCeNhv4vsLHCmnODcSzxgrZ3kDfhD00qHc0fozk1s97
fvnStQYAqdxc7mU8rsVRaX5xe+Sw5EisrhLYxxM0yg6HX08qbf7zzPxfz0zUux5n
gqs29ZmvJtr2UF8rewIDAQAB
-----END PUBLIC KEY-----
',

    ],

    // 服务器端 Server-Token 签字密钥
    'keys_for_token_signature' => '%cSMkR8gbr8y&3L03AiF&7D2W3@KfAlgi%',

    //下面是颁发给其他服务的密钥
    'issue_secret_for_service' => [
        'xxxx' => '25e2d2dff424b6963113fb82a3d53615',
    ],

    //其他服务颁发给本应用的密钥
    'signature_secret_for_service' => [
        'relation_engine' => '25e2d2dff424b6963113fb82a3d53615',
		'saber' => '6d4ef12261733ed547a40cac87e20417', //内部系统后台调用接口
		'ebike' => '30346876b3f45bab965823b9aa64a1a4', //内部系统后台调用接口
        'ebike_callback' => '0f2075852d10873da3c79ad9df774b26', //开锁/关锁回调
        'php_ebike_callback' => 'e288359803f58171f0cbc6716c818a94', //后台异常终止订单
        'ebike-backend' => 'b965823b9aa64a1a430346876b3f45ba', //运营后台
        'ebike-geo' => '30346876b3f45bab965823b9aa64a1a4', //ebike-geo
    ],

    //加密user_id的密钥
    'user_id_salt' => '62d0876d8bc4030269176e97735ea941',

    //加密deal_id
    'deal_id_salt' => 'aa24690128b54ad128a8731a87ccc389',
];
