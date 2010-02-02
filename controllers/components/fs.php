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

class FsComponent extends Object {

  var $controller = null;
  
  var $folder = null;
  
  var $root = ROOT;

        var $exclude = false;
  
  var $types = array(
    'text' => array('ctp', 'htm', 'html', 'log', 'php', 'sql', 'txt'),
    'archive' => array('zip')
    );
  
  function initialize(&$controller) {
    $this->controller = $controller;
    $this->folder =& new Folder();
  }
  
  function getPath() {
    $path = implode('/', $this->controller->params['pass']);
    return $path;
  }
  
  function getFsPath($path = false) {
    if (!$path) {
      $path = $this->controller->params['pass'];
    } elseif (is_string($path)) {
      $path = explode('/', trim($path, '/'));
    }

    $fsPath = false;
    if (is_string($this->root)) {
      $fsPath = $this->root . DS . implode(DS, $path);
    } elseif (is_array($this->root)) {
      if (count($this->root) > 1 && count($path) > 0) {
        foreach($this->root as $name => $root) {
          if (is_numeric($name) && basename($root) == $path[0]) {
            $fsPath = $root . DS . implode(DS, array_splice($path, 1));
            break;
          } elseif (strval($name) == $path[0]) {
            $fsPath = $root . DS . implode(DS, array_splice($path, 1));
            break;
          }
        }
      } elseif (count($this->root) == 1) {
        $fsPath = $this->root[0] . DS . implode(DS, $path);
      }
    } 
    if (!file_exists($fsPath)) {
      return false;
    }
    return $fsPath;
  }
  
  function getFileType($filename) {
    $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
    foreach ($this->types as $type => $extensions) {
      if (in_array($ext, $extensions)) {
        return $type;
      }
    }
    return 'unknown';
  }
  
  function getFileAttrs($path, $file) {
    return array(
      'size' => filesize($path . $file),
      'time' => filemtime($path . $file),
      'type' => $this->getFileType($file),
      'path' => $path,
      'writeable' => is_writable($path . $file)
      );
  }

  function check($file) {
    if (!$this->exclude) {
      return true;
    }
    foreach ((array)$this->exclude as $pattern) {
      if (preg_match("/$pattern/", $file)) {
        return false;
      }
    }
    return true;
  }

  function read($path) {
    $fsPath = $this->getFsPath($path);
    if (!$fsPath && is_array($this->root) && count($this->root) > 1) {
      $dirs = array();
      foreach($this->root as $name => $root) {
        if (is_numeric($name)) {
          $dirs[] = basename($root);
        } else {
          $dirs[] = $name;
        }
      }
      $files = array();
      return array($dirs, $files);
    }

    if (!is_dir($fsPath) || !$this->folder->cd($fsPath)) {
      return array(false, false);
    }
    $fsPath = Folder::slashTerm($fsPath);
    list($dirs, $files) = $this->folder->read();
    foreach ($dirs as $i => $dir) {
      if (!$this->check($dir)) {
        unset($dirs[$i]);
      }
    }
    $fileList = array();
    foreach($files as $file) {
      if (!$this->check($file)) {
        continue;
      }
      $fileList[$file] = $this->getFileAttrs($fsPath, $file);
    }
    return array($dirs, $fileList);
  }
  
  function readTree($fsPath = null) {
    if ($fsPath === null) {
      if (is_string($this->root)) {
        $fsPath = $this->root;
      } elseif (is_array($this->root) && count($this->root) == 1) {
        $fsPath = $this->root[0];
      } elseif (is_array($this->root) && count($this->root) > 1) {
        $tree = array();
        foreach($this->root as $name => $root) {
          if (is_numeric($name)) {
            $tree[basename($root)] = $this->readTree($root);
          } else {
            $tree[$name] = $this->readTree($root);
          }
        }
        return $tree;
      }
    }
    
    if (!$this->folder->cd($fsPath) || !$this->check($fsPath)) {
      return array();
    }
    list($dirs, $files) = $this->folder->read();
    $tree = array();
    foreach ($dirs as $dir) {
      if (!$this->check($dir)) {
        continue;
      }
      $tree[$dir] = $this->readTree(Folder::addPathElement($fsPath, $dir));
    }
    return $tree;
  }
}
?>