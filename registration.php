<?php

use Magento\Framework\Component\ComponentRegistrar;

/* Register module files */
ComponentRegistrar::register(
    ComponentRegistrar::MODULE, 'SamedayCourier_Shipping',
    isset($file) ? dirname($file) : __DIR__
);
