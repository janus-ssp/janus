Put this in your routing.yml
```yml
fos_oauth_server_token:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/token.xml"

fos_oauth_server_authorize:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/authorize.xml"
```

Put this in your config.yml
```yml
fos_oauth_server:
    db_driver: orm
    client_class:        Janus\OauthServer\Entity\Client
    access_token_class:  Janus\OauthServer\Entity\AccessToken
    refresh_token_class: Janus\OauthServer\Entity\RefreshToken
    auth_code_class:     Janus\OauthServer\Entity\AuthCode
```
