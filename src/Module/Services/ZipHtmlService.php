<?php

namespace Noking50\Modules\Required\Services;

use Noking50\Modules\Required\Exceptions\ZipHtmlException;
use Noking50\FileUpload\Facades\FileUpload;
use File;
use ZipArchive;

class ZipHtmlService {

    protected $baseDir;

    public function __construct() {
        $this->baseDir = 'html';
    }

    public function extract($fileinfo) {
        $path_src = FileUpload::getRootDirTmp() . $fileinfo['dir'] . DIRECTORY_SEPARATOR . $fileinfo['id'] . '.' . $fileinfo['ext'];
        $path_extract = FileUpload::getRootDirTmp() . $fileinfo['dir'] . DIRECTORY_SEPARATOR . $fileinfo['id'];
        if (File::exists($path_extract)) {
            File::deleteDirectory($path_extract);
        }
        File::makeDirectory($path_extract, 0777, true, true);
        $zip = new ZipArchive();
        if ($zip->open($path_src) === true) {
            $zip->extractTo($path_extract);
            $zip->close();
        }
        if (!File::exists($path_extract . DIRECTORY_SEPARATOR . $fileinfo['name'])) {
            throw new ZipHtmlException(trans('module_required::exception.zip_html.file_dir'));
        }
        if (!File::exists($path_extract . DIRECTORY_SEPARATOR . $fileinfo['name'] . DIRECTORY_SEPARATOR . 'index.html')) {
            throw new ZipHtmlException(trans('module_required::exception.zip_html.file_index'));
        }
    }

    public function move($fileinfo, $html_dir) {
        $path_src = FileUpload::getRootDirTmp() . $fileinfo['dir'] . DIRECTORY_SEPARATOR . $fileinfo['id'] . DIRECTORY_SEPARATOR . $fileinfo['name'];
        $path_dest = $this->getHtmlBasePath() . DIRECTORY_SEPARATOR . trim($html_dir, DIRECTORY_SEPARATOR);
        if (File::exists($path_dest)) {
            File::deleteDirectory($path_dest);
        }
        File::makeDirectory($path_dest, 0777, true, true);
        File::copyDirectory($path_src, $path_dest);
    }

    public function deleteUploadExtract($fileinfo) {
        $path_extract = FileUpload::getRootDirTmp() . $fileinfo['dir'] . DIRECTORY_SEPARATOR . $fileinfo['id'];
        if (File::exists($path_extract)) {
            File::deleteDirectory($path_extract);
        }
    }

    public function deleteUploadSource($fileinfo) {
        $path_src = FileUpload::getRootDirTmp() . $fileinfo['dir'] . DIRECTORY_SEPARATOR . $fileinfo['id'] . '.' . $fileinfo['ext'];
        if (File::exists($path_src)) {
            File::delete($path_src);
        }
    }

    public function htmlExist($html_dir) {
        $path_dest = $this->getHtmlBasePath() . DIRECTORY_SEPARATOR . trim($html_dir, DIRECTORY_SEPARATOR);
        
        return File::exists($path_dest . DIRECTORY_SEPARATOR . 'index.html');
    }

    public function getHtmlBasePath() {
        return rtrim(public_path($this->baseDir), DIRECTORY_SEPARATOR);
    }
    
    public function getHtmlUrl($html_dir) {
        $url = asset($this->baseDir . '/' . trim($html_dir, '/') . '/index.html');
        
        return $url;
    }
}
