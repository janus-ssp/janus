# Messenger for subscriptions

## Introduction

All changes made to the entities in JANUS are logged and are displayed to all users who subscribe to the changes in the Inbox view. But these notifications can also be delivered to external sources, using an external messenger. JANUS is shipped with a very basic mail messenger, that can deliver these notifications to the users email address instead.

## Configuring an external messenger


All external messengers are defined in the option `messenger.external` and is done using arrays:

```yml
messenger
    external:
        MESSENGERID:
            class: MESSENGERCLASS,
            name: MESSENGERNAME,
            option: MESSENGEROPTIONS,
```

For example the basic configuration of the mail extension look like the following:

```yml
messenger:
        # Default type for subscriptions
        default: INBOX
        external:
            mail:
                class: 'janus:SimpleMail'
                name: Mail
                option:
                    headers: "
MIME-Version: 1.0\r\n 
Content-type: text/html; 
charset=iso-8859-1\r\n
From: JANUS <no-reply@example.org>\r\n
Reply-To: JANUS Admin <admin@example.org>\r\n
"
```




Remember to change the from and reply-to email addresses. In the `headers`option you can add all valid email headers that you have a need for.

You can configure as many external messengers as you like, just remember to give them unique ids.

## Options

|**Name**|**Value**|**Required**|
|--------|---------|------------|
|class|the class that implements the messenger|Yes|
|name|A human readable name for the messenger|Yes|
|option|Options specific for the messenger given as an *array*|Yes|

# Setting the default external messenger

By setting the `messenger.default` option in the configuration file, you can control what external messenger is used by default when new subscriptions are created. Valid values are all defined `MESSENGERID` values.

# Implementing an external messenger

The most basic implementation of an external messenger requires on class placed in `<PATH TO JANUS>/lib/Messenger/`. The class must be called `sspmod_janus_Messenger_<MESSENGERNAME>`and must extend the class `sspmod_janus_Messenger`. It must contain to methods `__construct` and `send`.
```php
    class sspmod_janus_Messenger_MESSENGERNAME {
        protected function __construct(array $option) {}
        public function send(array $data) {}
    }
```
----
## ``php __construct``

| **Parameters** | *array* $option |
| -------------- | --------------- |
| **Return**     | *void*          |

The option `$option` to the constructor is the `option` array from the configuration. 

----
## send

| **Parameters** | *array* $data |  
| -------------- | ------------- |
| **Return**     | *boolean*     |  

The option `$data` parsed to the send method contains 3 keys:
- uid - Uid of the user who triggerd the notification
- subject - The subject of the notification
- message - The notification it self

----

From the `send` method you can hook into external libraries.
