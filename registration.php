<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE, 'SamedayCourier_Shipping',
    isset($file) ? dirname($file) : __DIR__
);
