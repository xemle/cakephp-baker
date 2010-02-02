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

class ZipComponent extends Object {
  
  var $name = 'ZipComponent';
  var $controller = null;
  var $zip = null;
  var $_stats = null;

  function startup(&$controller) {
    $this->controller = $controller;
    if (class_exists('ZipArchive')) {
      $this->zip = new ZipArchive();
    }
  }

  function unzip($file, $dst = false) {
    if (!$this->zip) {
      $this->log("Missing plugin for class ZipArchive");
      return false;
    }
    if (!is_readable($file)) {
      $this->log("File $file is not readable");
      return false;
    }

    if (!$dst) {
      $dst = dirname($file);
    } elseif (!is_dir($dst)) {
      $this->log("Destination $dst is not a directory");
      return false;
    }
    $dst = Folder::slashTerm($dst);

    if (!is_writeable($dst)) {
      $this->log("Destiantion $dst is not writeable");
      return false;
    }

    if ($this->zip->open($file) !== true) {
      $this->log("Could not open file $file");
      return false;
    } else {
      $this->log("Open $file with {$this->zip->numFiles} file(s)");
    }

    $this->_stats = array();
    $bytes = 0;
    for ($i = 0; $i < $this->zip->numFiles; $i++) {
      $stat = $this->zip->statIndex($i);
      $bytes += $stat['size'];
      $this->_stats[] = $stat;
    }

    $newFiles = array();
    foreach ($this->_stats as $file) {
      $newFile = $this->_extract($file, $dst);
      if ($newFile) {
        $newFiles[] = $newFile;
      }
    }
    $this->zip->close();
    return $newFiles;
  }

  /** Exract file from zip file 
    @param file Array of file stat
    @param dst Destination of filei
    @result filename on success */
  function _extract($file, $dst) {
    $fp = $this->zip->getStream($file['name']);
    if (!$fp) {
      $this->log("Could not extract {$file['name']}");
      return false;
    }
    if (dirname($file['name']) != '') {
      $folder = new Folder();
      $fileDir = $dst.dirname($file['name']);
      if (!is_dir($fileDir)) {
        if (!$folder->mkdir($fileDir)) {
          $this->log("Could not create directory $fileDir");
          return false;
        } else {
          $this->log("Create directory $fileDir");
        }
      }
    }

    // skip directories, which have zero size
    if ($file['size'] === 0) {
      $this->log("Skip directory {$file['name']}");
      return false;
    }

    $newFile = $dst.$file['name'];
    $tp = fopen($newFile, 'w');
    if (!$tp) {
      fclose($fp);
      $this->log("Could not open file $newFile");
      return false;
    }

    $written = 0;
    while (!feof($fp)) {
      $buf = fread($fp, 1024);
      $written += fwrite($tp, $buf);
    }
    fclose($fp);
    fclose($tp);
    if ($written != $file['size']) {
      $this->log("Extraction error: File has {$file['size']} Bytes but $written Bytes were written");
      unlink($newFile);
      return false;
    }
    $this->log("Extracted {$file['name']} ($written Bytes)");
    return $newFile;
  }
}

?>