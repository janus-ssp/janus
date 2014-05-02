Add to your config.yml:
```yml
fos_rest:
    disable_csrf_role: ROLE_API
    param_fetcher_listener: true
    view:
        view_response_listener: 'force'
        formats:
            xml:  true
            json: true
        templating_formats:
            html: true
    format_listener:
        rules:
            - { path: ^/, priorities: [ html, json, xml ], fallback_format: ~, prefer_extension: true }
    exception:
        codes:
            'Symfony\Component\Routing\Exception\ResourceNotFoundException': 404
            'Doctrine\ORM\OptimisticLockException': HTTP_CONFLICT
        messages:
            'Symfony\Component\Routing\Exception\ResourceNotFoundException': true
    allowed_methods_listener: true
    access_denied_listener:
        json: true
    body_listener: true
    body_converter:
        enabled: true


nelmio_api_doc: ~
```

Add to your routing.yml:
```yml
JanusServiceRegistryRestApiBundle:
  resource: @JanusServiceRegistryRestApiBundle/Resources/config/routing.yml
  prefix:   /api

NelmioApiDocBundle:
  resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
  prefix:   /api/doc


```
