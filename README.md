# positive-gmail-check
check GMail for unread messages, with certainty

See installation prereqs and cautions further below.

This system requests Google OAUTH / OAUTH2 authority to access your GMail email metadata.  The purpose is to show a count of your unread emails.  I created 
this because, at the time, in late 2017 with Android 4.x, I could not trust a "pull" of email to show new messages.  Often I would pull and pull for hours
and not see messages on the GMail app.  I would have to use GMail.com for a reliable reading.  Given that pulling didn't work, "push" certainly didn't work 
for hours.

To this day (mid 2020 and Android 8.x), when a pull or push will show new emails is iffy, although the situation is much improved.   I've used this several 
to many times a day for 2.5 years (end of 2017 to mid 2020).  It works very near perfectly.  

I never really intended for anyone else to use it on my server.  On one hand, you're welcome to.  On the other hand, I make no guarantees about 
it.  You're also free to run your own copy.

With that said, it is running at https://kwynn.com/t/7/12/email/  .  It will immediately take you to an OAUTH screen.
******
RUNNING STUFF - A DEVELOPMENT (NOT LIVE) ISSUE

The following only happens in development, because the live version has been very, very reliable.  The probably may be a conflict between live and 
dev.  I suspect I am not handling an error properly.

There is a situation in which the system breaks and the solution is to delete the OAUTH tokens, probably both in sessions and the gootokens 
(or whatever I called it) table / collection.  Specifically, I think a $code keeps going through ahd through repeatedly--the redirect gets 
called repeatedly--but the system still won't auth.

Ideally I would track this down; we'll see.

It looks like I only keep auth data in the go / goo collection when there is a refresh token; otherwise I keep it only in the sessions 
collection.  In this case I mean the final auth data--access tokens and refresh tokens.
*****
INSTALLATION STUFF

**
Be careful what file you set the debugger on.  I think it needs to be set on index.  
Setting it on main.php can lead to an infinite redirect loop
Update: I'm now less sure that was part of my problem, but I'll leave the caution there.
**
PREREQS

* my library
* composer stuff
* redirect / OAUTH2 URL stuff
* firewall stuff

*****
MY LIB

/opt/kwynn is a clone of https://github.com/kwynncom/kwynn-php-general-utils
*********

COMPOSER

I am using /opt/composer for my composer library, and /opt/kwynn assumes that.

You need Google libraries and MongoDB libraries, and MongoDB for that matter.  I'm unfortunately going to skip the MongoDB stuff here.
Because I had to update Google, here it is:

The composer commands below in turn create apiclient-services.

composer require google/auth
composer require google/apiclient

So, when I'm done:
ls /opt/composer/vendor/google
apiclient  apiclient-services  auth

***********
OAUTH2 / redirect URL

The redirect / OAUTH2 url needs to be registered with the Google Cloud Console and locally.  Generally, locally would be the 
file that has your client_secret and such.  I put that in MongoDB, though.

In the Google Cloud Console, the URL is part of the credentials screen for the specific app credentials.  (An app can have multiple credentials.)

In the qemail database:

db.getCollection('kwtom_files').find({})

There should only be one entry.

Under file / web / redirect_urls, the url must be there 
If you have multiple URLs, they must be there AND IN THE SAME ORDER AS IN THE GOOGLE CLOUD CONSOLE!
Otherwise you get:
{"error":"redirect_uri_mismatch","error_description":"Bad Request"}Thu, 27 Jan 2022 01:52:04 -0500

You'll get an "invalid grant" if you try to use an old auth code, where old is a matter of a few dozen ms.

***********
FIREWALL

If you're on your testing machine, the redirect URL must be open to the web.
The domain must route too, of course.

sudo ufw allow https

You might want http, too, to test the IP before you set to domain:

sudo ufw allow http

If you're dealing with an AT&T or perhaps other home router, see my 2022/01/12 blog entry, especially regarding IPv6:

https://kwynn.com/t/7/11/blog.html

**********

HISTORY

2022/01/27 02:16 - I just created a debugging branch.  The README file for that is useful.  I will remerge at least the README some time or another.
    UPDATE: I think this is the merged README, or close enough.  
