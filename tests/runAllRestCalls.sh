#!/bin/bash

# Temporary test script to call all REST calls, useful for debugging and finding out how many database queries the REST interface causes

#TODO: method_getUser
#TODO: method_getMetadata
#TODO: method_getAllowedSps
#TODO: method_findIdentifiersByMetadata

restUrls[1]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=getSpList&keys=&janus_key=engine&userid=engine&janus_sig=2b41bfad0d91ac66a1a63a7ab334c6b094f28e4b1effba501a26051db0b44c60e48b9c2f0ee9e949acf8d35a5b0469697b68632bc4c0e8d345eced77af57d1d7&rest=1'
restUrls[2]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=getIdpList&keys=&janus_key=engine&userid=engine&janus_sig=1eb334cdf64cc3accf49f3b2c60bcc476730b08ba6c41de32d0e7e91e1256d5c88041299370703ddd3ed4d177f54de5cd01dca31dbd0e86ef873361508fbd839&rest=1'
restUrls[3]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=getAllowedIdps&spentityid=https%3A%2F%2Fprofile.demo.openconext.org%2Fsimplesaml%2Fmodule.php%2Fsaml%2Fsp%2Fmetadata.php%2Fdefault-sp&janus_key=engine&userid=engine&janus_sig=aa523154448f918483f5f29ab5f55998d4b34dec5fe1f545b86ed0af87a039350be814a0cd98624c2296c5e5999d8e3e5edc6a01da807b93d7bdd33ae5694ccc&rest=1'
restUrls[4]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=getEntity&entityid=http%3A%2F%2Fmock-idp&janus_key=engine&userid=engine&janus_sig=3e9febf59bb34541e54f3fc4d5c5bcb058efadbee1486b4764d39e92b18ed91f8d8aede203d1041bc0dd2a9e8583cfc091fb492dc2da03d4f379ecbfce38657c&rest=1'
restUrls[5]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=isConnectionAllowed&spentityid=https%3A%2F%2Fprofile.demo.openconext.org%2Fsimplesaml%2Fmodule.php%2Fsaml%2Fsp%2Fmetadata.php%2Fdefault-sp&idpentityid=http%3A%2F%2Fmock-idp&janus_key=engine&userid=engine&janus_sig=657945a3042e85fade4c4f3f3ded47289db2bf02843e2a23c3124599173e41c727717340f1af9668408a3ba30132af634c16f89fb05709f65573a8766342b736&rest=1'
restUrls[6]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=arp&entityid=https%3A%2F%2Fprofile.demo.openconext.org%2Fsimplesaml%2Fmodule.php%2Fsaml%2Fsp%2Fmetadata.php%2Fdefault-sp&janus_key=engine&userid=engine&janus_sig=9fd87dcb01edc47616827acc2982362898fe181c1d93d4dbdfa0db31531cb9503d971688eaa91c41f61643e73bf8d1ae6ccde9f724ffc983a7db9f77e6ead4a1&rest=1'
restUrls[7]='https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/?method=getEntity&entityid=https%3A%2F%2Fprofile.demo.openconext.org%2Fsimplesaml%2Fmodule.php%2Fsaml%2Fsp%2Fmetadata.php%2Fdefault-sp&janus_key=engine&userid=engine&janus_sig=5bbc2a20072b8dc05e05ce77b6eb35fcc1a900d1b248bbbd8836cf74357ec2c56ca83b43333d083e673edc1d662c00f8ba8aa34dfb377e11eae6c98e299bb53a&rest=1'

for url in "${restUrls[@]}"
do
   :
   # -k means ignore certificate warnings
   echo "calling $url\n"
   curl -k $url > /dev/null
done
