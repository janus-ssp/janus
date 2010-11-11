<?php
include('/home/test/janus-dev/modules/janus/lib/UIguard.php');

class UIguardTest extends PHPUnit_Framework_TestCase
{
    public function testHasPermission()
    {
        $permissionmatrix = array(
            'permission1' => array(
                'default' => FALSE,
                'test' => array(
                    'role' => array(
                        'admin',
                    ),
                ),
            ),
        );

        $uig = new sspmod_janus_UIguard($permissionmatrix);

        $this->assertTrue($uig->hasPermission('permission1', 'test', array('admin')));
    }
}
