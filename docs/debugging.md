Debugging tips
--------------

When developing Janus there might be some unexpected 'magic' happening you do not understand.
This might be due to listeners or subscribers who silently do their job in the background without the developer knowing about them.

To find out which subscribers and listeners are active
```sh
./app/console container:debug:listener
```

Or to find out which custom janus subscribers and listeners are active
```sh
./app/console container:debug:listeners | grep Janus
```

Known listeners
The following listeners are known:
- AuditPropertiesUpdater: sets various so called 'audit properties' like: user, ip, date when storing Doctrine entities in the database.
See https://github.com/janus-ssp/janus/blob/master/src/Janus/ServiceRegistry/Doctrine/Listener/AuditPropertiesUpdater.php for more info
- TablePrefixListener: Prefixes tables names of Doctrine entities with prefix from config (janus__ by default).
See https://github.com/janus-ssp/janus/blob/master/src/Janus/ServiceRegistry/Doctrine/Extensions/TablePrefixListener.php for more info.
- AddAuthenticatedUserProcessor: adds the name of the logged in user to the log entry metadata
See: https://github.com/janus-ssp/janus/blob/master/src/Janus/ServiceRegistry/Log/AddAuthenticatedUserProcessor.php
