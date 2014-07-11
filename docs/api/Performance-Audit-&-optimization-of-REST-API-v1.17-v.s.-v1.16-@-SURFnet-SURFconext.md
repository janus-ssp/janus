Version 1.0 (4th April 2014) by Boy Baukema.

# History & Goal
In order to preserve or improve performance of the mission critical REST API for SURFnet SURFconext (an implementation of OpenConext). Pieter van der Meulen requested a informal Performance Audit comparing API performance of v1.17 to that of v1.16.0 as it pertains to the platform.

The central question this report attempts to answer is: 
**Will the new version of Janus perform as well or better than the current version for SURFconext?**

# Conclusion
With modifications all calls to 1.7.3 are now faster with the exception of:
* getSpList and getIdpList when there is no cache (which is only after a deployment or a special circumstance)
* getUser, which is not used by any SURFconext client

The answer to the question:

**Will the new version of Janus perform as well or better than the current version for SURFconext?**

is:

**Yes** (most likely).

# Approach
Performance testing is an inexact science at best, small differences in the application usage, setup or environment may have large effects on it's operation.
To ensure reliable results great care was given to ensure that the System Under Test (SUT) resembles the production system (which for obvious reasons is unavailable for testing) in these aspects as closely as possible.

# Setup & Environment
A version of OpenConext-serviceregistry (SR) 3.9.1 (containing JANUS 1.16.0) is set up on the following URL:

https://serviceregistry-janus-test-old.test.surfconext.nl

A version of OpenConext-serviceregistry (SR) 4.0.0 (containing JANUS 1.17.2) is set up on:

https://serviceregistry-janus-test-new.test.surfconext.nl

Both using the same instance of the Apache webserver and the MySQL database server. 
Both using their own Memcached server.
Both using their own schema filled with production data, appropriately migrated for that version of SR.

The (virtual private) server, while not as well equipped as the production platform, is comparable to production in setup.

Both instances of SR are configured to resemble production.

# Tools

* [New Relic Insight](https://newrelic.com) is used for server monitoring ([specific account](https://rpm.newrelic.com/accounts/649531)).
* [XHProf](http://xhprof.io) and [XHGui](https://github.com/perftools/xhgui) for performance drill downs ([add xhgui.test.surfconext.nl to your hosts file](https://xhgui.test.surfconext.nl)).
* [Custom written JANUS API Testing tools](https://github.com/janus-ssp/janus/wiki/API-testing-differences-before-and-after-ORM-introduction).

# Production Usage
There appear to be 2 users (their implementation is out of scope for this document): 
1. engineblock
2. csa

## 1. EngineBlock (EB)
4 nodes running EB appear to be, every 4 hours refreshing their results for 'getIdpList' and 'getSpList' the last of which triggers a 'arp' call per Service Provider.
Unfortunately 1 node will not do 1 get*List, but instead it will rely on users to trigger a cache expiry, meaning that 1 node may do 1 call or may do multiple depending on the load of EB and of SR.

Furthermore per entity as it's cache expires:
* findIdentifiersByMetadata
* getAllowedIdps
* getEntity
* isConnectionAllowed
This is distributed across time, however does depend on the load of EB (higher load, means multiple users triggering the same cache expiry).

This leads to the conclusion that the 'arp' method is highly performance critical.

## 2. CSA
4 nodes running CSA appear to be doing a 'getAllowedSps' call for every known Idp and for the resulting Sps do a 'arp' call (note that this is NR_OF_IDPS + (NR_OF_IDPS * NR_OF_SPS) calls).
And the occasional 'findIdentifiersByMetadata'.
2 nodes do their calls at the same time, while 2 others are a different intervals (though this is not guaranteed).

This leads to the conclusion that the 'arp' method is highly performance critical.

# Results

## 1.7.2, clean cache, 1 simultaneous request, max 10 entities

Results (per call in percentage of time taken for new response as measured on old response, sorted by percentage):

    [getSpList] => 386
    [getIdpList] => 372
    [getUser] => 113
    [getEntity] => 110
    [arp] => 107
    [getMetadata] => 107
    [getAllowedSps] => 100
    [isConnectionAllowed] => 97
    [getAllowedIdps] => 72
    [findIdentifiersByMetadata] => 20

Here we can see the following:
* getSpList and getIdpList are significantly slower.
* getUser, getEntity, **arp**, getMetadata are slightly slower.
* getAllowedSps, isConnectionAllowed, getAllowedIdps are equal or slighly faster.
* findIdentifiersByMetadata is 5x faster.

## 1.7.2, with cache, 1 simultaneous request, max 10 entities

    [getUser] => 120
    [getEntity] => 109
    [arp] => 107
    [getAllowedSps] => 106
    [getSpList] => 105
    [getMetadata] => 104
    [isConnectionAllowed] => 103
    [getIdpList] => 88
    [getAllowedIdps] => 84
    [findIdentifiersByMetadata] => 27

Note that a cache (which is the 'normal' state for an instance) dramatically improves the get*List calls, however the majority of the calls, notably the arp call, are still slower than the previous version.


## 1.7.2, with cache, 10 simultaneous requests, max 10 entities

    [getUser] => 206
    [arp] => 142
    [getMetadata] => 142
    [getEntity] => 137
    [getAllowedSps] => 110
    [isConnectionAllowed] => 103
    [getSpList] => 97
    [getIdpList] => 94
    [getAllowedIdps] => 89
    [findIdentifiersByMetadata] => 19

Note that with more concurrency the 'arp' call is now significantly slower than on the 1.6.0 version.

## 1.7.3, clean cache, 1 simultaneous request, max 10 entities

    [getSpList] => 418
    [getIdpList] => 281
    [getUser] => 94
    [getEntity] => 91
    [arp] => 91
    [getAllowedIdps] => 90
    [getMetadata] => 90
    [getAllowedSps] => 87
    [isConnectionAllowed] => 83
    [findIdentifiersByMetadata] => 31

After performance improvements in 1.7.3 all calls other than the get*List calls are now faster.

## 1.7.3, with cache, 1 simultaneous request, max 10 entities

    [getUser] => 101
    [getMetadata] => 96
    [getAllowedSps] => 94
    [getEntity] => 93
    [arp] => 92
    [getIdpList] => 92
    [isConnectionAllowed] => 88
    [getAllowedIdps] => 85
    [getSpList] => 68
    [findIdentifiersByMetadata] => 32

And with caching even the get*List calls are faster.

## 1.7.3, with cache, 10 simultaneous request, max 10 entities

    [getUser] => 110
    [getSpList] => 96
    [getIdpList] => 84
    [getAllowedSps] => 82
    [getEntity] => 79
    [arp] => 77
    [getMetadata] => 75
    [getAllowedIdps] => 71
    [isConnectionAllowed] => 65
    [findIdentifiersByMetadata] => 20

With added concurrency all calls remain faster except getUser (which is not used by SURFconext).