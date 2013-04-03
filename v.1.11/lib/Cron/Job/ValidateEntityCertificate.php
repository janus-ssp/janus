<?php
/**
 *
 */

/**
 *
 */
class sspmod_janus_Cron_Job_ValidateEntityCertificate extends sspmod_janus_Cron_Job_Abstract
{
    const CONFIG_WITH_TAGS_TO_RUN_ON = 'validate_entity_certificate_cron_tags';

    public function __construct()
    {
    }

    public function runForCronTag($cronTag)
    {
        if (!$this->_isExecuteRequired($cronTag)) {
            return array();
        }

        $cronLogger = new sspmod_janus_Cron_Logger();
        try {
            $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
            $srConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
            $rootCertificatesFile = $srConfig->getString('ca_bundle_file');

            $util = new sspmod_janus_AdminUtil();
            $entities = $util->getEntities();

            foreach ($entities as $partialEntity) {
                try {
                    $entityController = new sspmod_janus_EntityController($janusConfig);

                    $eid = $partialEntity['eid'];
                    if (!$entityController->setEntity($eid)) {
                        $cronLogger->with($eid)->error("Failed import of entity. Wrong eid '$eid'.");
                        continue;
                    }

                    $entityController->loadEntity();
                    $entityId = $entityController->getEntity()->getEntityid();
                    $entityType = $entityController->getEntity()->getType();
                    try {
                        try {
                            $certificate = $entityController->getCertificate();

                            // @workaround
                            // Since getCertificate() returns false when certificate does not exist following check is required to skip validation
                            if(empty($certificate)) {
                                throw new Exception('No certificate found');
                            }
                        }
                        catch (Exception $e) {
                            if ($entityType === 'saml20-sp') {
                                $cronLogger->with($entityId)->notice("SP does not have a certificate");
                            }
                            else if ($entityType=== 'saml20-idp') {
                                $cronLogger->with($entityId)->warn("Unable to create certificate object, certData missing?");
                            }
                            continue;
                        }
                        $validator = new sspmod_janus_OpenSsl_Certificate_Validator($certificate);
                        $validator->setIgnoreSelfSigned(true);
                        $validator->validate();

                        $validatorWarnings = $validator->getWarnings();
                        $validatorErrors = $validator->getErrors();
                        foreach ($validatorWarnings as $warning) {
                            $cronLogger->with($entityId)->warn($warning);
                        }
                        foreach ($validatorErrors as $error) {
                            $cronLogger->with($entityId)->error($error);
                        }

                        sspmod_janus_OpenSsl_Certificate_Chain_Factory::loadRootCertificatesFromFile($rootCertificatesFile);

                        $chain = sspmod_janus_OpenSsl_Certificate_Chain_Factory::createFromCertificateIssuerUrl($certificate);
                        $validator = new sspmod_janus_OpenSsl_Certificate_Chain_Validator($chain);
                        $validator->setIgnoreSelfSigned(true);
                        $validator->setTrustedRootCertificateAuthorityFile($rootCertificatesFile);
                        $validator->validate();

                        $validatorWarnings = $validator->getWarnings();
                        $validatorErrors = $validator->getErrors();
                        foreach ($validatorWarnings as $warning) {
                            $cronLogger->with($entityId)->warn($warning);
                        }
                        foreach ($validatorErrors as $error) {
                            $cronLogger->with($entityId)->error($error);
                        }
                    } catch (Exception $e) {
                        $cronLogger->with($entityId)->error($e->getMessage());
                    }
                } catch (Exception $e) {
                    $cronLogger->error($e->getMessage() . $e->getTraceAsString());
                }
            }
        } catch (Exception $e) {
            $cronLogger->error($e->getMessage() . $e->getTraceAsString());
        }

        if ($cronLogger->hasErrors()) {
            $this->_mailTechnicalContact($cronTag, $cronLogger);
        }
        return $cronLogger->getSummaryLines();
    }
    
    protected function _isExecuteRequired($cronTag)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');

        $cronTags = $janusConfig->getArray(self::CONFIG_WITH_TAGS_TO_RUN_ON, array());

        if (!in_array($cronTag, $cronTags)) {
            return false; // Nothing to do: it's not our time
        }
        return true;
    }
}
