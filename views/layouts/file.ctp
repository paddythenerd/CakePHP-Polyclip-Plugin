<?php

if( $thumb ) {
    $mimeType = $attachment['Image']['mimetype'] ;
    $file = $attachment['AttachmentThumbnail']['path'] ;
} else {
    $mimeType = $attachment['Attachment']['mimetype'] ;
    $file = $attachment['Attachment']['path'] ;
}

$here = dirname( dirname( dirname( dirname( dirname( __FILE__ )))));
$filePath = $here . $file ;

header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT"             ); // Date in the past
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
header( "Cache-Control: post-check=0, pre-check=0", false    );
header( "Pragma: no-cache"                                   ); // HTTP/1.0

// The next 2 headers are required to make IE work over HTTPS.
// More info:
//   http://in2.php.net/manual/en/function.header.php#74736
//   http://eirikhoem.wordpress.com/2007/06/15/generated-pdfs-over-https-with-internet-explorer/
header( "Cache-Control: maxage=3600" );
header( "Pragma: public" );

header( "Content-Disposition: filename=" . basename( $filePath ));
header( "Content-type: " . $mimeType );

readfile( $filePath );

?>
