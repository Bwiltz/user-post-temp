=== GrassBlade xAPI Companion ===
Tags: GrassBlade, Tin Can API, xAPI, Experience API
Requires at least: 3
Tested up to: 5.3.2
Contributors: Pankaj Agrawal

First Tin Can Plugin on Wordpress. Host Tin Can API Content on Wordpress, track user data. Supports packages from Articulate, Lectora, DominKnow, iSpring and more.

== Description ==

#### GrassBlade xAPI Companion 

GrassBlade xAPI Companion is a support tool for Experience API (xAPI) or Tin Can API. It can launch content inside an iframe by providing necessary parameters in the url. We have tested it tincan content published using Articulate and with demos provided by ADL. It can also send tracking statements to the LRS on Page View.

#### Features:
* One Click Content Upload
* Embed Tin Can package in wordpress post, pages, or custom posts. 
* Add Launch Link to Open in new window or a lightbox
* Statement Viewer
* H5P Integration
* Get and Set State using Shortcodes
* Direct upload from Dropbox
* Secure Tokens

#### Supports Tin Can Packages:
* Articulate Storyline
* Lectora Inspire
* DominKnow Claro
* iSpring Pro, and more

#### Supports Non-Tin Can Packages:
* Articulate Studio
* Captivate, and more

#### Integrates with LearnDash LMS
== Changelog ==
= 3.1.0 =
* Feature Added: Full User Report of all content for users. Along with support for Rich Quiz Report.
* Fixed: MPEG-DASH video not working. 

= 3.0.4 =
* Fixed: Show latest completion status in the completion message.
* Bug Fixes

= 3.0.3 = 
* Bug fixes

= 3.0 = 
* Feature: Added SCORM Support along with Completion Tracking and xAPI Statements generation
* Feature: Show Rich Quiz Report in Results table and on LearnDash Profile page 
* Feature: Show a congratulations message on xAPI/SCORM Content completion when completion tracking is enabled and completion behaviour is not hide button.
* Feature Improvement: Completion Tracking: Mark both LearnDash Lesson as well as Topic as completed automatically, if Quiz under Topic is completed by xAPI Content, and everything else is completed
* Fixed: Completion Tracking: LearnDash Next Lesson Button was not visible if Lesson Progression was disabled
* Fixed: Content Security not working on some Windows based servers
* Fixed: Completion Behaviour: Mark Complete button was getting enabled even on a failed statement. Marking checks in the background before enabling the button. 

= 2.2.1 = 
* Fixed: LearnDash: Next Lesson button not showing on Lesson with completed quizzes if Course Progression is disabled & Lesson not yet completed.

= 2.2 =
* New advanced Completion Tracking features: Hide Button, Show button on completion, Enable button on completion, Auto-redirect on completion.
* Added GrassBlade Add-ons page
* Fixed: Course structure not updating on LRS if debug display is enabled.
* Fixed: Error on sending enrollment on bulk user import
* Fixed: Content Security not working on some Windows based servers

= 2.1.11 = 
* Fix access check bug with H5P on LearnDash
* Fix error on uploading via Internet Explorer

= 2.1.10 = 
* Fixed: H5P error in guest mode when used with Secure Tokens
* Fixed: Logout statements generated for guests (not logged in users)

= 2.1.9 =
* Fixed: Logout statements generated on cron job
* Fixed: Upload error for non-tracking content
* Other minor improvements and bug fixes


= 2.1.8 =
* Fixed: Dropbox Upload: error when no dropbox key added
* Fixed: Events Trackigs: don't use verb updated when post is deleted. Might change verbs for different statuses. 
* Feature: Add GrassBlade filter for filtering plugins related to GrassBlade

= 2.1.7 =
* Feature: New imporved direct uploader with progress bar, and new version of Dropbox Uploader
* Feature: Ability to upload videos. 
* Feature: Support for HLS (.m3u8) and MPEG-DASH (.mpd)
* Feature: Added Events tracking for: New Post Creation, Post Updation, User Login, User Logout, User Registration, User Deletion, User Enrollment in Course, User Unenrollment from Course, New Comment
* Change: Moved PageViews tracking settings to Events Tracking page
* Fixed: Upload related issues.
* Store versions information of xAPI Contents

= 2.0.5 =
* Fixed: Video Pro private videos, restricted for domains not work. 
* Other bug fixes

= 2.0.4 = 
* Fixed: Update button not working on xAPI Content page

= 2.0.3 = 
* Using Gutenberg editor for xAPI Content
* Fixed: Completion not working for xAPI Content added as sub block via Guttenberg Blocks inside accordion, tabs, columns, etc  
* Other bug fixes

= 2.0.2 = 
* Loading H5P modules In-Page using H5P's own shortcode, instead of embed link.
* Fixed: Next Lesson Link not showing in LD 3.0
* Other bug fixes

= 2.0 =
* Feature: Added xAPI Video Profile 1.0 support for advanced video analytics. Supports: Self-hosted Videos (mp4, etc), Audios (mp3, wav), YouTube, Vimeo.
* Feature: GrassBlade xAPI Companion Blocks for xAPI Content, LeaderBoard, and User Score
* Feature: Fluid responsive Lightbox as well as In Page content boxes, auto adjusting in desktop, Android as well as iOS
* Feature: Added Aspect Lock setting, so that the responsive adjustment is locked in a aspect ratio. In Page is always aspect locked. 
* Fixed WordPress REST API not working in some servers, specially FCGI based
* Add Select All/None option in Bulk Import
* Fixed: mark complete button visible on LearnDash 3.0
* Added grassblade_video_player filter for switching to old video player.
* Fixed: Secured Tokens not working when used with "Name and User ID" based User Identifier
* Use the WordPress Date/Time format in "Your Results" table and Leaderboard Table. 
* Security Fix
* Other bug fixes

= 1.6.7.2 =
* Fixed: Issue with WordPress REST API connection from GrassBlade LRS for some servers. 

= 1.6.7.1 =
* Fixed: LD3.0 Mark Complete button visible

= 1.6.7 = 
* Security Fix

= 1.6.6 = 
* Fix uploading of Articulate Rise non xAPI content

= 1.6.5 = 
* Bug Fixes

= 1.6.4 =
* Show trigger messages only when related functions are called via trigger
* Do not block content for completion of previous content if LearnDash Lesson Progression is disabled

= 1.6.3 = 
* Ability to change LearnDash Course Progress to In Progress if any xAPI Content has been started

= 1.6.2 = 
* Fix video play issue caused due to partial content fetch in xAPI Content with Content Security enabled. 
* Bug fixes

= 1.6.1 = 
* Bug fixes

= 1.6 = 
* Fixed: LearnDash Quiz sending grouping.id=false if quiz is not attached to a course
* Fixed: Bulk Settings upload not updating xapi_activity_id causing issue during completion tracking
* Fixed: Error on launching H5P content.
* Content auto sizing when weight/height is in %. 
* Improve Pass/Fail checking.
* Fixed: Not able to mark the lesson as complete when there is xAPI Content as well as Topic/Quiz on a Lesson.
* Fixed: When shared course steps is enabled, completion of xAPI Content marks the Lesson or Topic as complete even if child Topic/Quiz is not complete. 
* Fixed: several bugs

= 1.5.22 = 
* Fixed: xAPI based quiz completion not added to learndash activity table
* Fixed: several bugs

= 1.5.21 = 
* Fixed: WordPress HTTP API connection from GrassBlade LRS, and support for LearnDash course and content integration with GrassBlade LRS (Version >= 2.1.1.8)
* Fixed: several bugs

= 1.5.20 = 
* Fixed: LearnDash Lesson getting marked as completed if content on it is completed, even when there are incomplete quizzes under it. 

= 1.5.19 = 
* Fixed: Completion Tracking with Shared Course Steps in LearnDash not working.
* Fixed: Completion Tracking not working when actor type is account/user id.
 
= 1.5.17 = 
* Feature: Added actor_type parameter and User Identifier setting to select whether to use user email id or user id to send as identifier to the LRS. 
* Added error message when the LRS is using http and WordPress is using https. 

= 1.5.16 = 
* Fixed: content with completion tracking disabled should not restrict access to quiz

= 1.5.15 = 
* Fixed: completion behaviour and completed statement after xAPI quiz completion
* Auto generation of new registration value after every completion.
* Change Print Certificate link to button
* Fixed: Video using mp4 url
* Fixed: GrassBlade trying to mark lesson in unenrolled course, and user getting enrolled to course. Now, the mark complete will happen only when user is enrolled to course
* Fixed: several bugs

= 1.5.14 = 
* Fixed: 500 error on uploading content, and on editing content linked to other pages
* Updated Bulk Import feature with several changes

= 1.5.12 = 
* Fixed BadgeOS compatibility code issue
* Fixed few other bugs and minor adjustments.

= 1.5.11 = 
* Fixed LearnDash Quiz page with xAPI Content not getting marked as complete 
* Added time-from,time-to information in video related statements. And changed time format to seconds. 
* Show Next Lesson link on LearnDash pages with xAPI Content
* Added Bulk Import and Bulk Settings options under xAPI Content

= 1.5.10 =
* Added xAPI Statement to LRS for LearnDash Assignment upload. 

= 1.5.9 = 
* Added ability to track additional verbs. (Requires addon code)
* Fixed: Associated content not showing on xAPI Content edit page
* Fixed: Warning during update check
* Fixed: Vimeo not showing fullscreen button.

= 1.5.8 = 
* Show original activity id

= 1.5.7 =
* Fixed Secure Tokens 

= 1.5.6 = 
* Added LearnDash Topic Completion xAPI statements
* Fixed bug 

= 1.5.5 =
* Added LearnDash Quiz Tracking
* Allow Non-xAPI (non tracking) mode for Video
* Added BadgeOS Badge Earned Tracking to LRS
* Added BadgeOS Compatibility code.
* Added help text and other bug fixes.

= 1.5.4 = 
* Fixed tracking issue with emails having + sign in them
* Added more infomation/suggestion in exhaustive test on upload errors.

= 1.5.3 =
* Bug Fixes: Video Button not showing. Content Details getting erased on WordPress 4.4

= 1.5.2 = 
* Bug Fixes related to H5P Permissions

= 1.5.1 = 
* Bug Fixes related to H5P

= 1.5.0 =
* Secure Tokens
* H5P Integration
* LeaderBoard for xAPI Content
* Ability to place xapi content in any part of the page using shortcode.
* Groups integration with the LRS.
* Record and show scores and completion on any page, post, lesson or quiz.
* Ability to change the URL slug for xAPI Content.
* Support for non Tin Can version of Articulate Studio 13
* LearnDash Mark Complete button is gone
* Ability to disable Statement Viewer

= 1.4.1 = 
* Ability to upload image buttons instead of using text links 
* Ability to test errors in Completion Tracking setup

= 1.3 =
* Bug fixes
* Added Video Tracking for YouTube and Vimeo
* Added content security for static content.
* Added better error information and suggestions.
* Added GrassBlade LRS SSO.
* Removed Shortcode Generator. Shortcode would still work.

= 1.2 =
* Bug fixes
* Updated the way completion triggering works

= 1.1 =
* Added Meta box to easily select and add xAPI Content on any page/post
* Added completion tracking and mark completion integration with LearnDash Lessons/Topics/Quizzes
* Upgraded Statement Viewer to 1.0
* Added v1.0 option
* Added preview page for xAPI Contents
* Added shorter shortcode with only content id
* Made registration field static by default to support bug on Articulate related to resume/bookmark feature.
* Few minor bug fixes

= 0.7.1 =
* Bug fixes

= 0.7 =
* Feature to upload your package from Dropbox

= 0.6.2 =
* Fixed one click upload link for LearnDash Integration

= 0.6.1 =
* Bug fixes

= 0.6 =
* Added get_state and set_state shortcodes to utilize State API

= 0.5.3.4 =
* Internationalization capable code.
* Bug fixes

= 0.5.3.3 =
* Bug fixes
* Quiz completion reporting for LearnDash quizzes

= 0.5.3.2 =
* Bug fixes

= 0.5.3 =
* Lesson and Course Attempt and Completions of LearnDash LMS

= 0.5.1 =
* Added option to decide showing content on xAPI Content page

= 0.5 =
* Added option to open in a Lightbox
* Advanced Content Uploader
* Bug fixes.

= 0.4.2 =
* Added registration parameter
* Added support for categories in xAPI Content
* Added Referer in Page Views to track where the user came from.
* Changed Branding
* Bug fixes.

= 0.4.0 =
* Fix support for Articulate Storline after their changes.
* Added support for DominKnow Calro Tin Can package
* Added support for Lectora Inspire Tin Can package
* Added support for iSpring Tin Can package
* Added Statement Viewer
* Added activity_id
* Added target options to be able to choose from embeding content in page, or a Launch link.

= 0.3.0 =
* Added One click upload 

= 0.2.0 =
* Added short code generator

= 0.1.0 =
* Launch!
