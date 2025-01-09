@if (isset($lesson_details->lesson_type))
    @if ($lesson_details->lesson_type == 'text')
        <div class="course-video-area border-primary">
            <div class="text_show">
                {!! removeScripts($lesson_details->attachment) !!}
            </div>
        </div>
    @elseif ($lesson_details->lesson_type == 'video-url')
        <div class="course-video-area border-primary border">
            <!-- Video -->
            <div class="course-video-wrap">
                <div id="player">
                    <iframe src="{{ $lesson_details->lesson_src }}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
                </div>
                @include('course_player.player_config')
            </div>
        </div>
    @elseif($lesson_details->lesson_type == 'system-video')
        @php
            $watermark_type = get_player_settings('watermark_type');
            $lesson_video = $lesson_details->lesson_src;
            if ($watermark_type == 'ffmpeg') {
                $origin = dirname($lesson_details->lesson_src);
                $dir = $origin . '/watermark';
                $file = str_replace($origin, '', $lesson_details->lesson_src);
                $lesson_video = "{$dir}{$file}";
            }
        @endphp
        <div class="course-video-area border-primary border">
            <!-- Video -->
            <div class="course-video-wrap">
                <div class=" bd-r-10 mb-16 position-relative bg-light custom-system-video">
                    <video id="player" playsinline controls>
                        <source src="{{ asset($lesson_details->lesson_src) }}" type="video/mp4">
                    </video>
                    @include('course_player.player_config')
                </div>
            </div>
        </div>
    @elseif($lesson_details->lesson_type == 'image')
        @php
            $img = asset('uploads/lesson_file/attachment/' . $lesson_details->attachment);
        @endphp
        <img width="100%" class="max-w-auto" height="auto" src="{{ $img }}" />
    @elseif($lesson_details->lesson_type == 'vimeo-url' && $lesson_details->video_type == 'vimeo')
        @php
            $video_url = $lesson_details->lesson_src;
            $video_id = explode('https://vimeo.com/', $video_url);
            $video_id = str_replace('https://vimeo.com/', '', $video_url);
        @endphp

        <div class="course-video-area border-primary border">
            <!-- Video -->
            <div class="course-video-wrap">
                <div id="player">
                    <iframe height="500" src="https://player.vimeo.com/video/{{ $video_id }}?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media" allowfullscreen allowtransparency allow="autoplay"></iframe>
                    @include('course_player.player_config')
                </div>
            </div>
        </div>
    @elseif($lesson_details->lesson_type == 'google_drive')
        @php
            $video_url = $lesson_details->lesson_src;
            $url_array_1 = explode('/', $video_url . '/');
            $url_array_2 = explode('=', $video_url);
            $video_id = null;
            if ($url_array_1[4] == 'd'):
                $video_id = $url_array_1[5];
            else:
                $video_id = $url_array_2[1];
            endif;
        @endphp
        <div class="course-video-area border-primary border">
            <!-- Video -->
            <div class="course-video-wrap">
                <video width="100%" height="680" id="player" playsinline controls>
                    <source class="" src="https://www.googleapis.com/drive/v3/files/{{ $video_id }}?alt=media&key={{ get_settings('youtube_api_key') }}" type="video/mp4">
                </video>
                @include('course_player.player_config')
            </div>
        </div>
    @elseif($lesson_details->lesson_type == 'html5')
        <div class="course-video-area border-primary border">
            <!-- Video -->
            <div class="course-video-wrap">
                <video width="100%" height="680" id="player" playsinline controls>
                    <source class="remove_video_src" src="{{ $lesson_details->lesson_src }}" type="video/mp4">
                </video>
                @include('course_player.player_config')
            </div>
        </div>
    @elseif($lesson_details->lesson_type == 'document_type')
        @if ($lesson_details->attachment_type == 'pdf')
            <iframe id="myLesson_IF" style="pointer-events:unse; -webkit-touch-callout:none; -webkit-user-select:none; -khtml-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none; cursor: default; overflow-y:scroll;" id="fraDisabled" onload="disableContextMenu();" onMyLoad="disableContextMenu();" class="embed-responsive-item" width="100%" src="{{ asset('uploads/lesson_file/attachment/' . $lesson_details->attachment) }}#toolbar=0&navpanes=0" allowfullscreen></iframe>
            <div class="ext_pdf_mask"></div>
        @elseif($lesson_details->attachment_type == 'doc' || $lesson_details->attachment_type == 'ppt')
            <iframe id="myLesson_IF" style="pointer-events:unse; -webkit-touch-callout:none; -webkit-user-select:none; -khtml-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;  cursor: default; overflow-y:scroll;" id="fraDisabled" onload="disableContextMenu();" onMyLoad="disableContextMenu();" class="embed-responsive-item" width='100%' src="https://view.officeapps.live.com/op/embed.aspx?src={{ asset('uploads/lesson_file/attachment/' . $lesson_details->attachment) }}#toolbar=0&navpanes=0" frameborder='0'></iframe>
            <div class="ext_pdf_mask"></div>
        @elseif($lesson_details->attachment_type == 'txt')
            <iframe id="myLesson_IF" style="pointer-events:unse; -webkit-touch-callout:none; -webkit-user-select:none; -khtml-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;  cursor: default; overflow-y:scroll;" id="fraDisabled" onload="disableContextMenu();" onMyLoad="disableContextMenu();" class="embed-responsive-item" width='100%' src="{{ asset('uploads/lesson_file/attachment/' . $lesson_details->attachment) }}#toolbar=0&navpanes=0" frameborder='0'></iframe>
            <div class="ext_pdf_mask"></div>
        @endif
    @elseif($lesson_details->lesson_type == 'quiz')
        <div class="course-video-area border-primary pb-5">
            @include('course_player.quiz.index')
        </div>
    @else
        <iframe class="embed-responsive-item" width="100%" src="{{ $lesson_details->lesson_src }}" allowfullscreen></iframe>
    @endif
@endif


<style>


    .ext_pdf_mask
    {
        width:61%;
        height:552px;
        background:rgba(0, 0, 0, 0);
        position:absolute;
        top:0px;
    }

@media screen and (max-width:968px)
{
    .ext_pdf_mask
    {
        width:93.5%;
        height: 490px;
    }
}
@media screen and (max-width:768px)
{
    .ext_pdf_mask
    {
        width:100%;
    }
}
@media screen and (max-width:468px)
{
    .ext_pdf_mask
    {
        width:100%;
    }
}

-webkit-touch-callout:none;
-webkit-user-select:none;
-khtml-user-select:none;
-moz-user-select:none;
-ms-user-select:none;
user-select:none;
-webkit-tap-highlight-color:rgba(0,0,0,0);
}
img
{
  pointer-events: none;
}
div, a, button, span, p, img, input
{
    -webkit-tap-highlight-color: transparent;
 }
</style>
     <script language="JavaScript">

      
       window.onload = function () {
           document.addEventListener("contextmenu", function (e) {
               e.preventDefault();
           }, false);
           document.addEventListener("keydown", function (e) {
               //document.onkeydown = function(e) {
               // "I" key
               if (e.ctrlKey && e.shiftKey && e.keyCode == 73) {
                   disabledEvent(e);
               }
               // "J" key
               if (e.ctrlKey && e.shiftKey && e.keyCode == 74) {
                   disabledEvent(e);
               }
               // "S" key + macOS
               if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
                   disabledEvent(e);
               }
               // "U" key
               if (e.ctrlKey && e.keyCode == 85) {
                   disabledEvent(e);
               }
               // "F12" key
               if (event.keyCode == 123) {
                   disabledEvent(e);
               }
           }, false);
           function disabledEvent(e) {
               if (e.stopPropagation) {
                   e.stopPropagation();
               } else if (window.event) {
                   window.event.cancelBubble = true;
               }
               e.preventDefault();
               return false;
           }
       }
//edit: removed ";" from last "}" because of javascript error

  function disableContextMenu()
  {
    window.frames["fraDisabled"].document.oncontextmenu = function(){alert("Right Click disabled!"); return false;};   
    // Or use this
    // document.getElementById("fraDisabled").contentWindow.document.oncontextmenu = function(){alert("Right Click disabled!"); return false;};;    
  }  
</script>

