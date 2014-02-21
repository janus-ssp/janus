Put this in your AppKernel.php
```php
     new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
```
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
    client_class:        Janus\ServiceRegistry\Bundle\OauthServerBundle\Entity\Client
    access_token_class:  Janus\ServiceRegistry\Bundle\OauthServerBundle\Entity\AccessToken
    refresh_token_class: Janus\ServiceRegistry\Bundle\OauthServerBundle\Entity\RefreshToken
    auth_code_class:     Janus\ServiceRegistry\Bundle\OauthServerBundle\Entity\AuthCode
```
