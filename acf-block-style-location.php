<?php 
/*
Plugin Name: ACF Block Styles location
Plugin URI: https://github.com/davidwebca/acf-block-style-location
Description: WordPress and ACF plugin to allow a Field Group Location for every block style registered
License: MIT
Author: David Lapointe Gilbert
Version: 0.2
Author URI: https://davidweb.ca
Text Domain: acfbsl
Domain Path: /languages
*/

add_filter('acf/location/rule_types', function ($choices) {
    $block_styles_cache = get_transient('acf_block_styles');
    if(!$block_styles_cache) {
        return $choices;
    }

    $choices['Blocks'] = [];
    $choices['Blocks']['acf_block_style'] = 'Block Style';

    return $choices;
});


add_filter('acf/location/rule_values/acf_block_style', function ($choices) {
    $block_styles_cache = get_transient('acf_block_styles');
    if(!$block_styles_cache) {
        return $choices;
    }

    foreach ($block_styles_cache as $blockname => $styles) {
        foreach ($styles as $stylename => $style) {
            $stylekey = $blockname . ':' . $stylename;
            $stylefullname = $blockname . ' : ' . $style['name'];
            $choices[$stylekey] = $stylefullname;
        }
    }

    return $choices;
});

add_filter('acf/location/rule_match/acf_block_style', function ($match, $rule, $screen, $field_group) {
    if (!empty($screen['block']) && isset($_REQUEST['block'])){
        $block = $_REQUEST['block'];
        $block = wp_unslash( $block );
        $block = json_decode( $block, true );
        $className = isset($block['className']) ? $block['className'] : 'is-style-default';
        $style = get_block_style_from_class($className);
        $stylekey = $block['name'] . ':' . $style;

        $match = $rule['value'] == $stylekey;

        if($rule['operator'] == '!=') {
            $match = !$match;
        }
        
    }
    return $match;
}, 10, 4);

/**
 * Sometimes, ACF doesn't use location rule match for blocks because it doesn't need to
 * (no other location rules exist for blocks as of now)
 * so we need to use load_field_groups to remove them if they don't match
 *
 * Note: I think this is the part that doesn't work. Ajax does execute correctly
 * but the React Component doesn't force update its dom at every call for the form.
 * This must be something that we can do with ACF JS API somewhere, but can't fin it.
 */

add_filter('acf/load_field_groups', function($field_groups = []) {
    if(isset($_REQUEST['block'])) {
        $block = $_REQUEST['block'];
        $block = wp_unslash( $block );
        $block = json_decode( $block, true );
        $className = isset($block['className']) ? $block['className'] : 'is-style-default';
        $style = get_block_style_from_class($className);
        $stylekey = $block['name'] . ':' . $style;

        foreach ($field_groups as $groupkey => $group) {
            foreach ($group['location'] as $locationskey => $locs) {
                foreach ($locs as $lockey => $loc) {
                    if($loc['param'] == 'acf_block_style') {
                        $result = $loc['value'] == $stylekey;

                        if($loc['operator'] == '!=') {
                            $result = !$result;
                        }
                        
                        if(!$result) {
                            unset($field_groups[$groupkey]);
                        }
                    }
                }
            }
        }

    }
    return $field_groups;
}, 10, 1);

add_action('admin_enqueue_scripts', function(){
    // This cache can only get built in the gutenberg editor since block styles don't get registered otherwise... bug?
    // Make sure to visit a page that contains the gutenberg editor first
    // before assigning the location
    if(is_gutenberg_page()) {
        $block_types = acf_get_block_types();
        $block_styles_registry = \WP_Block_Styles_Registry::get_instance();
        $acf_block_styles = [];
        foreach($block_types as $blockname => $block_data) {
            $styles_for_block = $block_styles_registry->get_registered_styles_for_block($blockname);
            if(!empty($styles_for_block)) {
                $acf_block_styles[$blockname] = $styles_for_block;
            }
        }
        set_transient('acf_block_styles', $acf_block_styles);
    }
});

add_action('admin_footer', function( $hook ) {
    $block_styles_cache = get_transient('acf_block_styles');
    if(!$block_styles_cache) {
        // bail early if we didn't get block style cache
        return;
    }
    ?>
    <script>
        /**
         * https://stackoverflow.com/questions/29321742/react-getting-a-component-from-a-dom-element-for-debugging
         */
        function FindReact(dom, traverseUp = 0) {
            const key = Object.keys(dom).find(key=>{
                return key.startsWith("__reactFiber$") // react 17+
                    || key.startsWith("__reactInternalInstance$"); // react <17
            });
            const domFiber = dom[key];
            if (domFiber == null) return null;

            // react <16
            if (domFiber._currentElement) {
                let compFiber = domFiber._currentElement._owner;
                for (let i = 0; i < traverseUp; i++) {
                    compFiber = compFiber._currentElement._owner;
                }
                return compFiber._instance;
            }

            // react 16+
            const GetCompFiber = fiber=>{
                //return fiber._debugOwner; // this also works, but is __DEV__ only
                let parentFiber = fiber.return;
                while (typeof parentFiber.type == "string") {
                    parentFiber = parentFiber.return;
                }
                return parentFiber;
            };
            let compFiber = GetCompFiber(domFiber);
            for (let i = 0; i < traverseUp; i++) {
                compFiber = GetCompFiber(compFiber);
            }
            return compFiber.stateNode;
        }

        // call fetch when we change block style to update the form
        jQuery(document).on('click', '.block-editor-block-styles__item', function() {
            // preview mode selector
            let selector = '.block-editor-block-inspector .acf-block-component.acf-block-panel > div';
            let currentBlock = wp.data.select('core/block-editor').getSelectedBlock();
            if(currentBlock) {
                if(currentBlock.attributes.mode == 'edit') {
                    selector = '[data-block="' + wp.data.select('core/block-editor').getSelectedBlockClientId() + '"] .acf-block-component > div';
                }

                // get the BlockForm react instance
                let blockFormInstance = FindReact(document.querySelector(selector));
                blockFormInstance.fetch();
            }
        })

    </script>
    <?php
});
