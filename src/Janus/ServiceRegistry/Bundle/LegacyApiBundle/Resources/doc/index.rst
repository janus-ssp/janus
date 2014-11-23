This bundle is only a wrapper around the legacy (so called REST) api.

Purpose is to make the api available directly via Symfony instead of being routed through SimpleSamlPhp.

A reason to do this is performance. Even when routing via SimpleSamlPhp the Symfony kernel will be bootstrapped
since some services need to be retrieved from Symfony's service container.
When accessing the API directly via Symfony the SimpleSamlPhp bootstrap overhead can be bypassed.