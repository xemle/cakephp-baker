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

class BakerController extends AppController {
  var $name = 'Baker';
  
  var $components = array('Session', 'Security', 'Fs');
  
  var $helpers = array('Html', 'Form', 'Javascript', 'Number', 'Session', 'Fs');
  
  var $uses = null;
  
  function beforeFilter() {
    $this->Security->disabledFields = array('Fs.file');
    // $this->Fs->root = array(ROOT . DS . 'baker', ROOT . DS . 'app');
    // $this->Fs->exclude = array('.svn', '.diff$', 'users');
    parent::beforeFilter();
  }
  
  function index() {
    if (!empty($this->data)) {
      if ($this->data['Fs']['action'] != 'none' && $this->data['Fs']['path'] != 'none') {
        if ($this->data['Fs']['action'] == 'cd') {
          $this->redirect('index/' . $this->data['Fs']['path']);
        }
      }
    }
    $path = $this->Fs->getPath();
    list($dirs, $fileList) = $this->Fs->read($path);
    $fsPath = $this->Fs->getFsPath($path);
    if ($path && !$fsPath) {
      $this->Session->setFlash("Could not read $path");
      $this->redirect('index');
    } elseif ($path && !is_dir($fsPath)) {
      $this->redirect(null, 403);
    }
    if ($path) {
      $path .= '/';
    }
    
    if (is_writeable($fsPath)) {
      $canCreateFile = true;
    } else {
      $canCreateFile = false;
    }

    if (!$this->Session->check('fs.tree')) {
      $tree = $this->Fs->readTree();
      $this->Session->write('fs.tree', $tree);
    } else {
      $tree = $this->Session->read('fs.tree');
    }

    $this->set(compact('dirs', 'fileList', 'path', 'canCreateFile', 'tree'));
    $this->data = null;
                $this->title = basename($path);
  }
  
  function view() {
    $path = $this->Fs->getPath();
    $fsPath = $this->Fs->getFsPath();
    if (!is_readable($fsPath) || is_dir($fsPath)) {
      $this->redirect(null, 505);
    }
    
    $file =& new File($fsPath);
    $content = $file->read($file);
    $attrs = $this->Fs->getFileAttrs(dirname($fsPath) . DS, basename($fsPath));
    $this->set(compact('path', 'content', 'attrs'));
  }
  
  function edit() {
    if (!empty($this->data)) {
      $path = $this->data['Fs']['path'];
      $this->log("Path = $path");
      $fsPath = $this->Fs->getFsPath($path);
      $this->log("FsPath = $fsPath");
      if (!is_writeable($fsPath)) {
        $this->Session->setFlash("File $path is not writeable");
        $this->log("Redirect to view/".$path);
        $this->redirect('view/' . $path);
      }
      $file =& new File($fsPath);
      $file->write($this->data['Fs']['content']);
      $this->Session->setFlash("File $path saved");
    }
    $path = $this->Fs->getPath();
    $fsPath = $this->Fs->getFsPath($path);
    if (!is_readable($fsPath) || is_dir($fsPath)) {
      $this->log("Could not read fsPath ($fsPath)");
      $this->redirect('index');
    }
    
    $file =& new File($fsPath);
    $content = $file->read($file);
    $attrs = $this->Fs->getFileAttrs(dirname($fsPath) . DS, basename($fsPath));
    $this->set(compact('path', 'content', 'attrs'));
  }
  
  function create() {
    $path = $this->Fs->getPath();
    if (!empty($this->data)) {
      $fsPath = $this->Fs->getFsPath();
      if (!$fsPath || !is_dir($fsPath)) {
        $this->log("Could not read $path");
        $this->Session->setFlash("Could not read $path");
        $this->redirect('index');
      }
      if (!is_writeable($fsPath)) {
        $this->log("Can not create file here: $fsPath");
        $this->Session->setFlash("Can not create file here: $fsPath");
        $this->redirect('index/' . $path);
      }
      $fsPath = Folder::slashTerm($fsPath);
      if ($this->data['Fs']['isDir']) {
        if (@mkdir($fsPath . $this->data['Fs']['name'])) {
          $this->Session->setFlash("Created directory {$this->data['Fs']['name']}");
          $this->Session->delete("fs.tree");
          $this->redirect('index/' . $path . '/' . $this->data['Fs']['name']);
        } else {
          $this->log("Could not create directory {$this->data['Fs']['name']}");
          $this->Session->setFlash("Could not create directory {$this->data['Fs']['name']}");
          $this->redirect('index/' . $path);
        }
      } else {
        $file =& new File($fsPath . $this->data['Fs']['name']);
        if ($file->write('')) {
          $this->Session->setFlash("Created file {$this->data['Fs']['name']}");
          $this->log('edit/' . $path . '/' . $this->data['Fs']['name']);
          $this->redirect('edit/' . $path . '/' . $this->data['Fs']['name']);
        } else {
          $this->log("Could not create file {$this->data['Fs']['name']}");
          $this->Session->setFlash("Could not create file {$this->data['Fs']['name']}");
          $this->redirect('index/' . $path);
        }
      }
    }
    $this->redirect('index/' . $path);
  }
  
  function delete() {
    $path = $this->Fs->getPath();
    $fsPath = $this->Fs->getFsPath();
    if (!$fsPath) {
      $this->redirect('index');
    }
    if (!is_dir($fsPath)) {
      $file =& new File($fsPath);
      if (!$file->delete()) {
        $this->Session->setFlash("Could not delete $path");
      } else {
        $this->Session->setFlash("$path deleted");
      }
      $this->redirect('index/' . dirname($path));
    } else {
      $folder =& new Folder($fsPath);
      if (!$folder->delete()) {
        $this->Session->setFlash("Could not delete $path");
        $this->redirect('index/' . $path);
      } else {
        $this->Session->setFlash("$path deleted");
                                $this->Session->delete("fs.tree");
        $this->redirect('index/' . dirname($path));
      }
    }
  }

  function upload() {
    $path = $this->Fs->getPath();
    $fsPath = $this->Fs->getFsPath();
    if (!$fsPath || !is_dir($fsPath) || !is_writeable($fsPath) || empty($this->data)) {
      $this->Session->setFlash("Could not upload here");
    }
    if ($this->data['Fs']['file']['error'] != 0) {
      $this->Session->setFlash("Error while uploading file");
    }
    if (!@move_uploaded_file($this->data['Fs']['file']['tmp_name'], Folder::slashTerm($fsPath) . $this->data['Fs']['file']['name'])) {
      $this->Session->setFlash("Could not copy uploaded file");
    } else {
      $this->Session->setFlash("Uploaded file " . $this->data['Fs']['file']['name']);
    }
    $this->redirect('index/' . $path);
  }  
}
?>
