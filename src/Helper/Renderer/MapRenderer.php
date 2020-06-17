<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace Ivory\GoogleMap\Helper\Renderer;

use Ivory\GoogleMap\Helper\Formatter\Formatter;
use Ivory\GoogleMap\Helper\Renderer\Control\ControlManagerRenderer;
use Ivory\GoogleMap\Helper\Renderer\Utility\RequirementRenderer;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\MapTypeId;
use Ivory\JsonBuilder\JsonBuilder;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class MapRenderer extends AbstractJsonRenderer
{
    /**
     * @var MapTypeIdRenderer
     */
    private $mapTypeIdRenderer;

    /**
     * @var ControlManagerRenderer
     */
    private $controlManagerRenderer;

    /**
     * @var RequirementRenderer
     */
    private $requirementRenderer;

    public function __construct(
        Formatter $formatter,
        JsonBuilder $jsonBuilder,
        MapTypeIdRenderer $mapTypeIdRenderer,
        ControlManagerRenderer $controlManagerRenderer,
        RequirementRenderer $requirementRenderer
    ) {
        parent::__construct($formatter, $jsonBuilder);

        $this->setMapTypeIdRenderer($mapTypeIdRenderer);
        $this->setControlManagerRenderer($controlManagerRenderer);
        $this->setRequirementRenderer($requirementRenderer);
    }

    /**
     * @return MapTypeIdRenderer
     */
    public function getMapTypeIdRenderer()
    {
        return $this->mapTypeIdRenderer;
    }

    public function setMapTypeIdRenderer(MapTypeIdRenderer $mapTypeIdRenderer)
    {
        $this->mapTypeIdRenderer = $mapTypeIdRenderer;
    }

    /**
     * @return ControlManagerRenderer
     */
    public function getControlManagerRenderer()
    {
        return $this->controlManagerRenderer;
    }

    public function setControlManagerRenderer(ControlManagerRenderer $controlManagerRenderer)
    {
        $this->controlManagerRenderer = $controlManagerRenderer;
    }

    /**
     * @return RequirementRenderer
     */
    public function getRequirementRenderer()
    {
        return $this->requirementRenderer;
    }

    /**
     * @param RequirementRenderer $requirementRenderer
     */
    public function setRequirementRenderer($requirementRenderer)
    {
        $this->requirementRenderer = $requirementRenderer;
    }

    /**
     * @return string
     */
    public function render(Map $map)
    {
        $formatter = $this->getFormatter();
        $jsonBuilder = $this->getJsonBuilder();

        $options = $map->getMapOptions();
        unset($options['mapTypeId']);

        if (!$map->isAutoZoom()) {
            if (!isset($options['zoom'])) {
                $options['zoom'] = 3;
            }
        } else {
            unset($options['zoom']);
        }

        $this->controlManagerRenderer->render($map->getControlManager(), $jsonBuilder);

        if (!$map->getControlManager()->hasZoomControl()) {
            $options['zoomControl'] = false;
        }

        if (!$map->getControlManager()->hasStreetViewControl()) {
            $options['streetViewControl'] = false;
        }

        if (!$map->getControlManager()->hasScaleControl()) {
            $options['scaleControl'] = false;
        }

        if (!$map->getControlManager()->hasFullscreenControl()) {
            $options['fullscreenControl'] = false;
        }

        if (!$map->getControlManager()->hasRotateControl()) {
            $options['rotateControl'] = false;
        }

        if (!$map->getControlManager()->hasMapTypeControl()) {
            $options['mapTypeControl'] = false;
        }

        $jsonBuilder
            ->setValue(
                '[mapTypeId]',
                $this->mapTypeIdRenderer->render($map->getMapOption('mapTypeId') ?: MapTypeId::ROADMAP),
                false
            )
            ->setValues($options);

        return $formatter->renderObjectAssignment($map, $formatter->renderObject('Map', [
            $formatter->renderCall(
                $formatter->renderProperty('document', 'getElementById'),
                [$formatter->renderEscape($map->getHtmlId())]
            ),
            $jsonBuilder->build(),
        ]));
    }

    /**
     * @return string
     */
    public function renderRequirement()
    {
        return $this->requirementRenderer->render($this->getFormatter()->renderClass());
    }
}