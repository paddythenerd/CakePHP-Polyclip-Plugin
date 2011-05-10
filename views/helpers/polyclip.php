<?php

class PolyclipHelper extends AppHelper {

    var $helpers = array( 'Form', 'Html' );

    private $imageMimeTypes = array(
            'image/gif',
            'image/jpg',
            'image/jpeg',
            'image/png',
            );

    /**
     * formFile
     *
     * A wrapper around FormHelper::file()
     *
     * @param options Standard array of options to be passed to FormHelper::file() 
     * @return FormHelper file element
     */
    public function formFile( $options=array() ) {
        return $this->Form->file('Attachment.upload', $options);
    }


    /**
     * link
     *
     * A wrapper around HtmlHelper::link()
     *
     * @param attachableData Data array returned from an "Attachable" model's read() method
     * @param options Array of values for 'alt', 'thumbnail', and 'link_text'
     * @return HtmlHelper link element
     */
    public function link( $attachableData, $options=array() ) {

        $url = $this->url( $attachableData );

        // if attachment is an image
        if( !empty( $attachableData['Attachment']['mimetype'] ) &&
            in_array( $attachableData['Attachment']['mimetype'], $this->imageMimeTypes ))
        {
            $alt = !empty( $options['alt'] ) ? $options['alt'] : 'image' ;
            $thumbnail = !empty( $options['thumbnail'] ) ? $options['thumbnail'] : 'square' ;
            $thumbImg = $this->image( $attachableData, array( 'thumbnail'=>$thumbnail, 'alt'=>$alt ));
            $link = $this->Html->link( $thumbImg, $url, array( 'escape'=>false));

        } else {
            $text = !empty( $options['link_text'] ) ? 
                    $options['link_text'] : 
                    basename( $attachableData['Attachment']['path'] );
            $link = $this->Html->link( __($text, true), $url );
        }

        return $link ;

    }// end link()


    /**
     * image
     *
     * A wrapper around HtmlHelper::image()
     *
     * @param attachableData Data array returned from an "Attachable" model's read() method
     * @param options Optional array with a value for 'alt'
     * @return HtmlHelper image element
     */
    public function image( $attachableData, $options=array() ) {

        $url = $this->url( $attachableData, $options );
        $alt = !empty( $options['alt'] ) ? $options['alt'] : "image" ;

        return $this->Html->image( $url, array( 'alt'=>$alt ));

    }// end image()


    /**
     * url
     *
     * Generate a URL to an attachment using the Polyclip plugin's controller
     *
     * @param attachableData Data array returned from an "Attachable" model's read() method
     * @param options Optional array with a value for 'thumbnail'
     * @return string the URL
     */
    public function url( $attachableData, $options=array() ) {
        $routeAlias = Configure::read('Polyclip.route_alias');
        if( empty( $routeAlias )) { $routeAlias = "/polyclip" ; }

        $attachmentId = !empty( $attachableData['Attachment']['id'] ) ? $attachableData['Attachment']['id'] : null ;
        $url = $routeAlias."/attachment/index/".$attachmentId ;

        if( !empty( $options['thumbnail'] ) &&
                !empty( $attachableData['Thumbnail']['Attachment'][$options['thumbnail']] ))
        {
            $url .= "/thumb:".$options['thumbnail'] ;
        }

        return $url ;
    }// end url()

}// end class

