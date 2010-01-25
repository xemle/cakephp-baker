<?php 
/*
 * Baker.
 * 
 * Simple Online Text Editor for CakePHP
 * 
 * Copyright (C) 2010 Sebastian Felis, sebastian@phtagr.org
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 of the 
 * License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/** Filesystem helper */
class FsHelper extends AppHelper {

  var $helpers = array('Html');
  
  function pathLink($path, $action = 'index') {
    $paths = explode('/', $path);
    
    $link = $action;
    $links = array();
                $links[] = $this->Html->link('root', $action);
    foreach($paths as $name) {
      $link .= '/' . $name;
      $links[] = $this->Html->link($name, $link);
    }

    return $this->output(implode(' / ', $links));
  }
  
  function treeToSelect($tree, $path = '', $level = 0) {
    if (!count($tree)) {
      array();
    }
    $options = array();
    foreach($tree as $dir => $subTree) {
      $options[$path . '/' . $dir] = str_repeat("&nbsp;&nbsp;", $level * 2) . $dir;
      $subOptions = $this->treeToSelect($subTree, $path . '/' . $dir, $level + 1);
      $options = am($options, $subOptions);
    }
    ksort($options);
    return $options;
  }
}
?>