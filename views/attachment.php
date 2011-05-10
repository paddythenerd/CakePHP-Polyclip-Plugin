<?php

App::import('Core', 'Media');

/**
 * MediaView
 *
 * A wrapper around Cake's core MediaView
 *
 */
class AttachmentView extends MediaView {

    public function __construct( &$controller ) {
        parent::__construct( $controller );
    }

    /**
     * render
     *
     * Overrride Cake's core MediaView::render() method, which has a couple of shortcomings.
     * Namely: MediaView::render() doesn't send Content-disposition unless "download" is true, in
     * which case supplying 'name' has no effect on file name.
     */
    public function render() {
        $name = $download = $extension = $id = $modified = $path = $size = $cache = $mimeType = null;
        extract($this->viewVars, EXTR_OVERWRITE);

        if ($size) {
            $id = $id . '_' . $size;
        }

        if (is_dir($path)) {
            $path = $path . $id;
        } else {
            $path = APP . $path . $id;
        }

        if (!file_exists($path)) {
            header('Content-Type: text/html');
            $this->cakeError('error404');
        }

        if (is_null($name)) {
            $name = $id;
        }

        if (is_array($mimeType)) {
            $this->mimeType = array_merge($this->mimeType, $mimeType);
        }

        if (isset($extension) && isset($this->mimeType[$extension]) && connection_status() == 0) {
            $chunkSize = 8192;
            $buffer = '';
            $fileSize = @filesize($path);
            $handle = fopen($path, 'rb');

            if ($handle === false) {
                return false;
            }
            if (!empty($modified)) {
                $modified = gmdate('D, d M Y H:i:s', strtotime($modified, time())) . ' GMT';
            } else {
                $modified = gmdate('D, d M Y H:i:s') . ' GMT';
            }

            if ($download) {
                $contentTypes = array('application/octet-stream');
                $agent = env('HTTP_USER_AGENT');

                if (preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent)) {
                    $contentTypes[0] = 'application/octetstream';
                } else if (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent)) {
                    $contentTypes[0] = 'application/force-download';
                    array_merge($contentTypes, array(
                        'application/octet-stream',
                        'application/download'
                    ));
                }
                foreach($contentTypes as $contentType) {
                    $this->_header('Content-Type: ' . $contentType);
                }
                $this->_header(array(
                    'Content-Disposition: attachment; filename="' . $name . '.' . $extension . '";',
                    'Expires: 0',
                    'Accept-Ranges: bytes',
                    'Cache-Control: private' => false,
                    'Pragma: private'));

                $httpRange = env('HTTP_RANGE');
                if (isset($httpRange)) {
                    list($toss, $range) = explode('=', $httpRange);

                    $size = $fileSize - 1;
                    $length = $fileSize - $range;

                    $this->_header(array(
                        'HTTP/1.1 206 Partial Content',
                        'Content-Length: ' . $length,
                        'Content-Range: bytes ' . $range . $size . '/' . $fileSize));

                    fseek($handle, $range);
                } else {
                    $this->_header('Content-Length: ' . $fileSize);
                }
            } else {
                $this->_header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                if ($cache) {
                    if (!is_numeric($cache)) {
                        $cache = strtotime($cache) - time();
                    }
                    $this->_header(array(
                        'Cache-Control: max-age=' . $cache,
                        'Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache) . ' GMT',
                        'Pragma: cache'));
                } else {
                    $this->_header(array(
                        'Cache-Control: must-revalidate, post-check=0, pre-check=0',
                        'Pragma: no-cache'));
                }
                $this->_header(array(
                    'Content-Disposition: filename="' . $name . '.' . $extension . '";',
                    'Last-Modified: ' . $modified,
                    'Content-Type: ' . $this->mimeType[$extension],
                    'Content-Length: ' . $fileSize));
            }
            $this->_output();
            $this->_clearBuffer();

            while (!feof($handle)) {
                if (!$this->_isActive()) {
                    fclose($handle);
                    return false;
                }
                set_time_limit(0);
                $buffer = fread($handle, $chunkSize);
                echo $buffer;
                $this->_flushBuffer();
            }
            fclose($handle);
            return;
        }

        return false;

    }// end render()

}// end class


?>
