<?php


namespace Janus\ServiceRegistry;


class RevisionComparator
{
    public function compare($from, $to)
    {
        $result = new RevisionComparisonResult();
        return $result;
    }
}
