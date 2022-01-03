<?php

namespace Elbformat\SuluBehatBundle;

/**
 * Helper to find the difference in two complex array structures
 */
trait DiffTrait
{
    protected function arrayEquals($a, $b)
    {
        $this->getDiff($a, $b);
    }

    protected function arrayContains($container, $containment)
    {
        $this->getDiff($containment, $container, '', false);
    }

    /**
     * Check if two values/arrays are equal
     *
     * @param mixed  $a
     * @param mixed  $b
     * @param string $path
     * @param bool   $reverseCheck
     *
     * @throws \DomainException when differs
     */
    protected function getDiff($a, $b, $path = '', $reverseCheck = true)
    {
        if (!is_array($a) && !is_array($b)) {
            // Scalar values -> compare
            if ($a !== $b) {
                throw new \DomainException(sprintf('%s: %s != %s', $path, $a ?? 'null', $b ?? 'null'));
            }

            return;
        }

        if (!is_array($a) || !is_array($b)) {
            // One array -> mismatch
            throw new \DomainException(sprintf('%s: %s != %s', $path, gettype($a), gettype($b)));
        }

        // Only two arrays left
        foreach ($a as $k => $v) {
            $subpath = $path.'/'.$k;
            // Key does not exists in b
            if (!array_key_exists($k, $b)) {
                throw new \DomainException(sprintf('%s: Missing', $subpath));
            }

            // Recurse
            $this->getDiff($v, $b[$k], $subpath, $reverseCheck);

            // This key is done
            unset($b[$k]);
        }

        // Still entries left in b? -> unequal
        if ($reverseCheck && count($b)) {
            foreach ($b as $k => $v) {
                $subpath = $path.'/'.$k;
                throw new \DomainException(sprintf('%s: Extra', $subpath));
            }
        }
    }
}
