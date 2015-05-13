<?php

namespace Janus\ServiceRegistry\Saml2;

use DOMDocument;
use DOMElement;
use Psr\Log\LoggerInterface;
use SAML2_Const;
use SAML2_XML_md_EntityDescriptor;
use XMLReader;

class EntityDescriptorFactory {
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function createFromMetadataXml($saml2MetadataXml, $entityId)
    {
        $reader = new XMLReader();
        $canParse = $reader->XML($saml2MetadataXml);

        if (!$canParse) {
            $this->logger->notice('Unable to parse XML for import.', array('entityId' => $entityId));
            return false;
        }

        while ($reader->read()) {
            if ($reader->hasAttributes && $validUntil = $reader->getAttribute('validUntil')) {
                if (strtotime($validUntil) < time()) {
                    $this->logger->notice('May not parse XML, expired validUntil.', array('validUntil' => $validUntil));
                    return false;
                }
            }

            if ($reader->name !== 'EntityDescriptor') {
                continue;
            }
            if ($reader->namespaceURI !== SAML2_Const::NS_MD) {
                continue;
            }
            if (!$reader->hasAttributes) {
                continue;
            }

            $entityId = $reader->getAttribute('entityId');

            if ($entityId !== $entityId) {
                continue;
            }

            $document = new DOMDocument();
            $document->childNodes[0]->name;
            $importedNode = $document->importNode($reader->expand(), true);
            $document->appendChild($importedNode);
            $element = $document->getElementsByTagName('EntityDescriptor')->item(0);

            if (!$element instanceof DOMElement) {
                $this->logger->error('Unable to convert XMLReader DOMNode to DOMElement?');
                return false;
            }

            return new SAML2_XML_md_EntityDescriptor($element);
        }
        return false;
    }
}
