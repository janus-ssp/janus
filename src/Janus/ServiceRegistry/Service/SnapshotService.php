<?php

namespace Janus\ServiceRegistry\Service;

class SnapshotService
{
    private $dir;
    private $databaseHost;
    private $databaseName;
    private $databaseUsername;
    private $databasePassword;
    private $databasePort;

    public function __construct(
        $dir,
        $databaseName,
        $databaseUsername,
        $databasePassword = null,
        $databaseHost = null,
        $databasePort = null
    ) {
        $this->setDir($dir);

        $this->databaseName     = $databaseName;
        $this->databaseUsername = $databaseUsername;
        $this->databasePassword = $databasePassword;
        $this->databaseHost     = $databaseHost ? $databaseHost : 'localhost';
        $this->databasePort     = $databasePort ? (int) $databasePort : 3306;
    }

    public function setDir($dir)
    {
        $this->dir = $dir;

        if (file_exists($dir)) {
            return;
        }

        $created = mkdir($dir, 0700, true);
        if (!$created) {
            throw new \RuntimeException('Unable to create directory: ' . $dir);
        }
    }

    public function exists($id)
    {
        return file_exists($this->getPath($id));
    }

    public function find($id)
    {
        return array(
            'id' => $id,
            'created' => date('c', filectime($this->getPath($id))),
        );
    }

    public function create()
    {
        $id = date('YmdHis');
        $this->executeMysqlCommand('mysqldump', ' > ' . $this->getPath($id));

        return $id;
    }

    public function delete($id)
    {
        return unlink($this->getPath($id));
    }

    public function findList()
    {
        $snapshots = array();

        $files = glob($this->dir . DIRECTORY_SEPARATOR . 'snapshot-*');
        foreach ($files as $file) {
            $matches = array();
            if (!preg_match('/snapshot-(?P<id>\d+)/', $file, $matches)) {
                throw new \RuntimeException("Snapshot file with non-numeric id: " . $file);
            }

            $snapshots[] = array(
                'id' => $matches['id'],
                'created' => date('c', filectime($file)),
            );
        }
        return $snapshots;
    }

    public function restore($id)
    {
        return $this->executeMysqlCommand('mysql', ' < ' . $this->getPath($id));
    }

    private function getPath($id)
    {
        return $this->dir . DIRECTORY_SEPARATOR . 'snapshot-' . $id . '.sql';
    }

    private function executeMysqlCommand($command, $postfix = '')
    {
        $command .= ' -h ' . escapeshellarg($this->databaseHost);
        $command .= ' -P ' . $this->databasePort;
        $command .= ' -u ' . escapeshellarg($this->databaseUsername);
        $command .= ' --password=' . escapeshellarg($this->databasePassword);
        $command .= ' ' . $this->databaseName;
        $command .= $postfix;

        $output = array();
        $returnStatus = null;
        exec($command, $output, $returnStatus);

        if ((int)$returnStatus > 0) {
            throw new \RuntimeException(
                "Unable to execute \"$command\" gives return status '$returnStatus' and output: " . var_export($output, true)
            );
        }

        return true;
    }
}
