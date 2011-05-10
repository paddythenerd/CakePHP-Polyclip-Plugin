<?php

class AttachmentController extends AppController {

    var $name = 'Attachment' ;
    var $uses = array( 'Polyclip.Attachment', 'Polyclip.AttachmentThumbnail', 'Polyclip.ImageAttachment' );

    /**
     * index
     *
     * Action to output the requested attachment.
     *
     */
    public function index( $id=null ) {
        $this->view = 'Polyclip.Attachment' ;

        $attachment = $this->Attachment->read(null, $id);
        $file = $attachment['Attachment']['path'] ;
        $mimeType = $attachment['Attachment']['mimetype'];

        if( !empty( $this->params['named']['thumb'] )) {
            foreach( $attachment['AttachmentThumbnail'] as $nextThumb ) {
                if( $nextThumb['alias'] == $this->params['named']['thumb'] ) {
                    $file = $nextThumb['path'] ;
                    break ;
                }
            }
        }

        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ));
        if( empty( $extension )) { $extension = 'bin' ; }
        $params = array(
                'id'       => basename( $file ),
                'extension'=> strtolower( pathinfo( $file, PATHINFO_EXTENSION )),
                'name'     => basename( $file, '.'.pathinfo( $file, PATHINFO_EXTENSION )),
                'mimeType' => array( $extension => $mimeType ),
                'path'     => APP.dirname( $file ).DS,
                'download' => !empty( $this->params['named']['download'] ) ? true : false,
                'cache'    => !empty( $this->params['named']['cache'] ) ? true : false,
                );
        $this->set( $params );

    }// end index()


    /**
     * Alternative to index(), using custom layout "file"
     */
    public function download( $id=null, $thumb=null ) {
        $this->layout = 'file' ;
        if( $thumb ) {
            $this->AttachmentThumbnail->recursive = 0 ;
            $this->set( 'attachment', $this->AttachmentThumbnail->read(null, $id));
            $this->set( 'thumb', true );
        } else {
            $this->Attachment->recursive = 0 ;
            $this->set( 'attachment', $this->Attachment->read(null, $id));
            $this->set( 'thumb', false );
        }
    }// end download()

}

