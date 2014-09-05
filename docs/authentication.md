Authentication is done in various ways:
- The GUI uses SimpleSamlPhp authentication
- The old API uses a signature in the url based on the username/password in the database
- The new REST API uses HTTP Basic authentication based on the based on the username/password in the database

#Configuration


## admin.name *string*  
The name of the administrator of the installation
Example:
```yml
admin:
   name: admin
```

## admin.email *string*
The email address of the administrator
Example:
```yml
admin:
   email: admin@example.org
```

## auth *string*
The auth source used to gain access to JANUS. Must be configured in the `authsources.php` file in SSP
Example:
```yml
auth: user
```

## useridattr *string*
Name of the attribute used to uniquely identify the users. Must be unique across all users
Example:
```yml
useridattr: username
```
## user.autocreate *boolean*
Whether or not new uses should be auto created in JANUS
Example:
```yml
user:
    autocreate: true
```