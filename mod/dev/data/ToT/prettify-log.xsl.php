<?php
use Slothsoft\CMS\HTTPFile;

return HTTPFile::createFromDocument($this->getTemplateDoc('/dev/_tot-log'), 'prettify-log.xsl');