<?php

namespace SequentSoft\ThreadFlow\Enums\Messages;

enum TypingType: string
{
    case TYPING = 'typing';
    case UPLOAD_PHOTO = 'upload_photo';
    case RECORD_VIDEO = 'record_video';
    case UPLOAD_VIDEO = 'upload_video';
    case RECORD_AUDIO = 'record_audio';
    case UPLOAD_AUDIO = 'upload_audio';
    case UPLOAD_DOCUMENT = 'upload_document';
    case FIND_LOCATION = 'find_location';
    case RECORD_VIDEO_NOTE = 'record_video_note';
    case UPLOAD_VIDEO_NOTE = 'upload_video_note';
}
