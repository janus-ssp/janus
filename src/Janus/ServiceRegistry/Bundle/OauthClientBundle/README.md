Put this in your AppKernel.php
```php
     new Janus\ServiceRegistry\Bundle\OauthClientBundle\JanusOauthClientBundle(),
```

Put this in your parameters
```yml
oauth2_auth_server_url: https://apis.demo.openconext.org/
oauth2_auth_key: csa
oauth2_auth_secret: csa-secret
oauth2_auth_access_token: ca2b078e-3316-4bf9-8f46-26ed2fb8ca18
oauth2_allow_selfsign_cert: true
```

Put this in yoou security.yml
```yml
    access_control:
        - { path: ^/api, roles: ROLE_ACTIONS }

    role_hierarchy:
      ROLE_ADMIN:       ROLE_USER
      ROLE_API:         ROLE_USER
      ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    firewalls:
        oauth_token:
            pattern:    ^/oauth/v2/token
            security:   false

        oauth_authorize:
            pattern: ^/oauth/v2/auth
            form_login:
                provider: user_service
                check_path: /oauth/v2/auth_login_check
                login_path: /oauth/v2/auth_login
            anonymous: true


        api_secured:
            pattern:   /api/.*
            stateless: true
            fos_oauth:  true
```

