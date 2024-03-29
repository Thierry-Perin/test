<?php
/**
 * @version   $Id: RokSprocket_Layout_Sliders.php 11547 2013-06-18 20:17:15Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2019 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Sliders extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var array
	 */
	protected static $instanceHeadersRenderedTheme = array();

	/**
	 * @var string
	 */
	protected $name = 'sliders';

	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {
			$item->setPrimaryImage($this->setupImage($item, 'sliders_image_default', 'sliders_image_default_custom', 'sliders_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'sliders_link_default', 'sliders_link_default_custom', 'sliders_item_link'));
			$item->setTitle($this->setupText($item, 'sliders_title_default', false, 'sliders_item_title'));
			$item->setText($this->setupText($item, 'sliders_description_default', false, 'sliders_item_description'));

			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('sliders_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('sliders_previews_length', false);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);
		}
	}

	/**
	 * @return bool|string
	 */
	public function renderBody()
	{
		$theme_basefile = $this->container[sprintf('roksprocket.layouts.%s.themes.%s.basefile', $this->name, $this->theme)];
		return $this->theme_context->load($theme_basefile, array(
		                                                        'layout'     => $this,
		                                                        'items'      => $this->items,
		                                                        'parameters' => $this->parameters
		                                                   ));
	}

	/**
	 * Called to render headers that should be included on a per module instance basis
	 */
	public function renderInstanceHeaders()
	{
		RokCommon_Header::addStyle($this->theme_context->getUrl($this->theme . '.css'));
		RokCommon_Header::addScript($this->theme_context->getUrl($this->theme . '.js'));

		$id                       = $this->parameters->get('module_id');
		$settings                 = new stdClass();
		$settings->autoplay       = $this->parameters->get('sliders_autoplay', 1);
		$settings->delay          = $this->parameters->get('sliders_autoplay_delay', 5);
		$settings->height_control = $this->parameters->get('sliders_height_control', 'auto');
		$settings->height_fixed   = $this->parameters->get('sliders_height_fixed', 350);
		$options                  = json_encode($settings);

		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances.sliders.attach(" . $id . ", '" . $options . "');";
		$js[] = "});";
        $js[] = "window.addEvent('load', function(){";
        $js[] = "   var overridden = false;";
        $js[] = "   if (!overridden && window.G5 && window.G5.offcanvas){";
        $js[] = "       var mod = document.getElement('[data-accordion=\"" . $id . "\"]');";
        $js[] = "       mod.addEvents({";
        $js[] = "           touchstart: function(){ window.G5.offcanvas.detach(); },";
        $js[] = "           touchend: function(){ window.G5.offcanvas.attach(); }";
        $js[] = "       });";
        $js[] = "       overridden = true;";
        $js[] = "   };";
        $js[] = "});";
		RokCommon_Header::addInlineScript(implode("\n", $js) . "\n");
	}

	/**
	 * Called to render headers that should be included only once per Layout type used
	 */
	public function renderLayoutHeaders()
	{
		if (!self::$instanceHeadersRendered) {

			$root_assets = RokCommon_Composite::get($this->basePackage . '.assets.js');
			RokCommon_Header::addScript($root_assets->getUrl('moofx.js'));

			$instance   = array();
			$instance[] = "window.addEvent('domready', function(){";
			$instance[] = "		RokSprocket.instances.sliders = new RokSprocket.Sliders();";
			$instance[] = "});";

			RokCommon_Header::addInlineScript(implode("\n", $instance) . "\n");

			self::$instanceHeadersRendered = true;
		}
	}
}
