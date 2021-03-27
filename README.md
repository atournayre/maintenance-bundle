Maintenance bundle
=================
[![Build Status](https://travis-ci.com/atournayre/maintenance-bundle.svg?branch=master)](https://travis-ci.com/atournayre/maintenance-bundle)

The maintenance bundle helps managing software maintenance.

---

What this bundle for ?
----------------------
Use this bundle to enable/disable maintenance and manage options.

Getting Started
---------------
```
$ composer require atournayre/maintenance-bundle
```

Configuring
----------------------
Enable the bundle
```php
# config/bundles.php
return [
    // ...
    Atournayre\MaintenanceBundle\AtournayreMaintenanceBundle::class => ['all' => true],
    // ...
];
```

Configure .env
```php
# .env / .env.local.php
return array (
  // ...
  // MAINTENANCE_IS_ENABLED : true / false
  'MAINTENANCE_IS_ENABLED' => true,
  // MAINTENANCE_START_DATETIME : "2021-03-25 00:00:00" / "2021-03-25"
  'MAINTENANCE_START_DATETIME' => "2021-03-25 00:00:00",
  // MAINTENANCE_AUTHORIZED_IPS : "localhost,127.0.0.1"
  'MAINTENANCE_AUTHORIZED_IPS' => "localhost",
  // ...
);
```

Add configuration to services.yaml
```yaml
# config/services.yaml
parameters:
  atournayre_maintenance.is_enabled: '%env(MAINTENANCE_IS_ENABLED)%'
  atournayre_maintenance.start_date_time: '%env(MAINTENANCE_START_DATETIME)%'
  atournayre_maintenance.authorized_ips: '%env(MAINTENANCE_AUTHORIZED_IPS)%'
```

Overriding templates
---------------------

Using Symfony 4.4.* ?
```
$ mkdir -p templates/bundles/AtournayreMaintenanceBundle
$ cp -r vendor/atournayre/maintenance-bundle/Resources/views/. templates/bundles/AtournayreMaintenanceBundle
```
Templates are now ready for customization!

Usage
----------

### Schedule maintenance
Use command below to add maintenance in the future.
```
$ php bin/console maintenance --start="2021-01-01 12:00:00"
```

### Enable
Use command below to enable maintenance.
```
$ php bin/console maintenance --enable
```

### Disable
Use command below to disable maintenance.
```
$ php bin/console maintenance --disable
```

### Add authorized IP
If you want your application to be under maintenance except for specific IPs, use command below.

Works for IPv4 and IPv6.
```
$ php bin/console maintenance --add-ip="127.0.0.1"
```

### Clean IPs
Use command below to reset authorized IPs.
```
$ php bin/console maintenance --clean-ips
```

### Dump IPs
Don't remember which IPs are authorized ?

Use command below to dump authorized IPs.
```
$ php bin/console maintenance --dump-ips
```

### Debug
Use command below to view maintenance configuration.
```
$ php bin/console maintenance --debug
```

### Multiple
You can use multiple instructions at once.
```
$ php bin/console maintenance --enable --clean-ips --add-ip="127.0.0.1"
```
In this example, maintenance will be enabled, old authorized IPs will be reset and a new one will be added.