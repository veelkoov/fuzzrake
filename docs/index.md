# Fuzzrake - the software behind getfursu.it


### (Most) requested features

* Bookmarks/favourites/notifications
  * Issue: difficult to do without asking for e-mail / creating an account
  * To check: browser push notifications?
  * Bookmarks/favourites could be created based on local storage
    * Issue: but then just one device and susceptible to refreshes/cleans.
    * Would be possible with SSO without asking for e-mail/etc.
* Prices
  * Most probably will not happen. Parsing commissions statuses is difficult enough, while pricing is even more complex. Amount of possible errors and misunderstandings is too high.
  * Alternative: let makers set the prices directly on getfursu.it, possibly with a calculator/estimator of some kind.
* Ratings
  * Will not be implemented
  * Reason:
    * There are better websites for this
    * High risk of false, drama or displeased customer -based ratings

Glossary

- <em>website/profile/account</em> - your website(s) or social media account(s)/profile(s) like DeviantArt/Fur Affinity/Facebook/Instagram/Twitter and others
- <em>user</em> - getfursu.it visitor, potentially browsing your information and navigating further to your <em>website/profile/account</em>

Rules

1. One compliant <em>website/profile/account</em> is a minimum

    // TODO

2. Example fursuit work photos are required

    // You need examples of your past fursuit work creations on your <em>website/profile/account</em>. Unfortunately, yet obviously, art like pictures, badges, etc., is not fursuit-related. You won't join getfursu.it to find funding for your first creations. Consider your own fursuit or starting up with some pre-mades.

3. A way to contact you is required on your  <em>website/profile/account</em>

    // I am surprised I need to add this here.

4. No registration/logging-in requirement

    // <em>Websites/profiles/accounts</em> should not require your visitor to be logged in. This <strong>includes</strong> Fur Affinity, assuming you configured your user page that way. A prompt encouraging to register is just annoying, but a "login or register" screen is unacceptable.

5. No "work in progress" <em>websites/profiles/accounts</em>

    // E.g. websites missing most content, showing example pages, sample texts - these are not accepted. Some clients treat website-having makers as preferable and/or trustworthy. However, if you do not have a website yet, you'd better make sure you have some nice portfolio on DeviantArt/Fur Affinity/social first. You can fit any information, FAQ, TOS, and other stuff there as well. Creating a website is time-consuming and/or expensive. It's better to not have a website, than having one raising doubts/eyebrows.

6. (simple) English only, not even bilingual

    // Your <em>website/profile/account</em> doesn't have to be in English. However, all information here must be. If you don't speak English, please ask someone for help with translation and make sure to clearly state that you don't speak English in the dedicated section of the inclusion/update form.

7. No email/"open"/instant messaging contact info

    // E-mail address, phone number, Telegram, Messenger, Discord, Skype, etc. - such info belongs to your <em>website/profile/account</em>, and may (probably will) be removed from your submission.

8. Don't ever mention other studios/makers

    // You cannot claim to be friends with someone or (have) work(ed) for someone. No such statement should be accepted here without any verification, and due to limited workforce it is just not feasible. Such statements may get removed or your I/U submission may get rejected.

9. Photos must show exclusively your creations

    // You must not submit photos on which fursuits or other stuff created by others can be seen, e.g. taken during a convention. This could confuse the <em>user</em>.

10. Use common sense (i.a. don't cheat)

     // This list of rules deliberately skips stuff covered by common sense. For example, it should be obvious that you must not provide any false or misleading information or claim somebody else's work.

    Also, I am not a lawyer, and I will not waste my time on sophisticated wording or safe-netting everything. I expect users to act decent and respectfully without me having to write some kind of compendium.

11. Rules aren't final, can change, and are tracked

    // History of changes can be found on git. If you don't know how to use it, please contact me for assistance.


### Rules thoughts dump

* Inclusion requirements
  * Problems:
    * Maker has only a social media account, and they e.g. share work of others (*as a requirement to enter their raffle, not steal/impersonate*), a social account filled with non-fursuit-related stuff (hard to find the right photo)
    * No/little experience
      * How many finished projects required to include?
        * What is a "finished project"? A tail? A non-furred mask? A headbase?
          * A headbase is not a client wants, but getfursu.it could officially support maker resources (parts, resin stuff, bases etc.)
            * Aren't there websites for that? E.g. https://fursuitmaterials.com/ ?

* Requirements for websites
  * Possibly require one website dedicated (at least mostly) to the fursuit stuff?
    * Allow marking "primary fursuit websites"?
  * Allow posting other stuff to let show their talents and possibly attitude/values?

* Using photos with creation of other makers
  * Could mislead a user
  * Would require verification from the user
