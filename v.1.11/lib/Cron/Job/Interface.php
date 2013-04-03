<?php

interface sspmod_janus_Cron_Job_Interface
{
    public function runForCronTag($cronTag);
}