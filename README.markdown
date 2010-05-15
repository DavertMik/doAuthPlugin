doAuthPlugin
-------------
Inspired by sfGuardPlugin an easy-to-use new authorization system. doAuth is ready to work out of the box with just a few configuration changes. It takes all the common user functionality - User authorization by login and password, registration, activation by email, password reset. If you don't need sfGuard permissions system but a simple user module that works, you should try doAuth. Great for a new symfony 1.4 projects.

Features
--------
* All common user actions: authorization, registration, activation, password reset works out the box.
* Developer friendly: only 2 tables, model named 'User' that can be extended with Doctrine inheritance.
* Highly configurable and customizable. You can extend classes, add your event handlers or just edit configuration to create custom behavior.
* Standard emails are sent on registration, activation and password requests.
* Refactored from sfGuard. Shares similar concepts and some config APIs.

Coming Soon: doAccess plugin that adds a permissions functionality on top of doAuth plugin.

Installation
------------
  * Install the plugin

        $ symfony plugin:install doAuthPlugin

  * Build your model and forms:

        $ symfony doctrine:build-model
        $ symfony doctrine:build-forms

  * Update your database:

        $ symfony doctrine:insert-sql

  * make myUser class typically located in app/frontend/lib to extend doAuthSecurityUser:

        [php]
        class myUser extends doAuthSecurityUser
        {

        }

  * Optionally add the &quot;Remember Me&quot; filter to `filters.yml` above the security filter:

        remember_me:
          class: doAuthRememberMeFilter

  * Enable [baseAuth] module in setting.yml of your frontend or skip it and start the customization.

  * Change the default login and secure modules in `settings.yml`

        login_module:           baseAuth
        login_action:           signin

        secure_module:          baseAuth
        secure_action:          secure

You are ready to use. Try to access /register, /login, /logout routes.
By default doAuth automaticaly signs user is on registration and sends email with username and password.

Usage
-----
Access your user model from a class User.
(well, is very common thing, but should be noted for sfGuardUser users)

* creating a user (example)

        [php]
        $user = new User();
        $user->setUsername('davert');
        $user->setPassword('symfony');
        $user->setEmail('doAuth843@davert.mail.ua');
        $user->save();

* accessing user session class (example in controller)

        [php]
        $user = $this->getUser();
        // retrieve current user object
        $user->getAccount();
        // get user Id
        $user->getUserId();
        // check if user is admin or superadmin
        $user->isAdmin();


Customization
-------------
* Extend the User model if you need to, in your schema.yml. Currently user model contains 'username', 'email', 'last_login', 'is_active', 'is_super_admin' fields and hashed password.
* Don't use baseAuth module. Create your own User module:

        $ symfony generate:module frontend user

* let userActions extend the doAuthActions class.

        [php]
        class userActions extends doAuthActions

* userActions now implements common actions: signin, signout, register, activate, reset password.
* disable [baseAuth] module in settings.yml if it is enabled.
* activate the standard routes in your frontendConfiguration class:

        [php]
        class frontendConfiguration extends sfApplicationConfiguration
        {
          public function configure()
          {
            $this->dispatcher->connect('routing.load_configuration', array('doAuthRouting', 'listenToRoutingLoadConfigurationEvent'));
          }
        }

* or create your own routes. Use a sample file located in plugins/doAuth/config/routing.samlpe.yml
* write your own email templates. Copy all _mail_* partials from plugins/doAuth/modules/baseAuth/templates to your user/templates and rewrite them.
* don't forget to set symfony default actions, like we did for baseAuth module

        login_module:           user
        login_action:           signin

        secure_module:          user
        secure_action:          secure

Registration
------------
You can extend registration form in your own way. Here are 2 typical cases.

* To add custom widgets or validators to RegisterForm. Create new RegisterUserForm class in your lib/forms folder.

        [php]
        class RegisterUserForm extends BaseRegisterUserForm {
          public function configure()
          {
            parent::configure();
            // extend your code here
          }
        }

Sometimes you need more complex schema. For example, register user with different profile types, for example: Client and Developer. In this case you need to embed a Client and Developer forms into RegistrationForm depending on request parameters. This can't be made just by extending registration class. In this case you can use an events to extend current Register action with your logic.

Use ['user.pre_register'] event to access registration action, get request parameters, extend form, do everything you need.

* Add this line to your frontendConfiguration class

        [php]
        $this->dispatcher->connect('user.pre_register', array('UserListener', 'registerWithRoles'));

* create your listener class (that will act as a controller) and make it handle this event

        [php]
        class UserListener {

          public static function registerWithRoles(sfEvent $event) {

            // here comes a userActions controller
            $controller = $event->getSubject();

                // waiting for 'developer' or 'client' value
            $role = $controller->getRequest()->getParameter('role');
            $user = $controller->form->getObject();

            // all what we need for this example:
            $formclass = $role.'Form';
            $embed_form = new $formclass($user->get(ucfirst($role)));

            $controller->form->embedForm('role',$embed_form);
          }
        }

Codes and Security
----------------------
doAuthPlugin generates hashes for remember filter, activation code, password reset code and a new password for user on request.

Here are the principles that doAuth follows to create user codes:

* doAuth uses CSRF secret key to generate unique codes for every symfony site.
* doAuth uses Salt, Email, Password fields from User record to generate codes.
* doAuth uses sha1 hash algorithm to create code from strings.
* doAuth generates random 10-symbol length password from all latin characters upper and lowercase and numbers on user request.

If you are unhappy with provided methods, you can override them, following this instructions:

* Copy doAuthTools.class.php located in plugins/doAuthPlugin/lib to your project lib folder.
* Rewrite all functions there to your own
* Clear symfony cache (yes, `symfony cc` thing)
* Now doAuth fully depends on your own implementation of this class.

(Great thanks to Laurent Bachelier and Andrei Dziahel)

Configuration
-------------

This options are stored in plugins/doAuth/config/app.sample.yml.
If you want to change some settings - copy them to your app.yml file.

        all:
          doAuth:
            # password encrypting algorithm
            algorithm_callable: sha1
            # function for delegating password check
            check_password_callable: false
            # coookie
            remember_cookie_name: doRemember
              # expiration time (in secs), currently 1 year
            remember_cookie_expiration_age: 31536000
            # use user activation
            activation: false
            # where to redirect after request for password reset
            reset_password_url: '@homepage'
            # signin redirect
            signin_url: '@homepage'
            #signout url
            signout_url: '@homepage'
            # register standard routes
            routes_register: true

          doAuth_register:
            # forward registration to next module
            # syntax: [module, action]
            forward: ~
            # or redirect to current path
            redirect_path: '@homepage'
            # auto sign in after registration
            signin: true

          doAuth_email:
            # activate by email if activation is on
            activation: true
            # send registration notification
            registration: true
            # sender email
            from: mailer@currenthost.com
            # module where email partials are stored.
            # default is module from controller
            module: false

Events
------

Here is a list of all events that are fired by doAuthPlugin:

* user.signed_in - on sign in. Subject is doAuthSecurityUser class.
* user.pre_register - runs before the registration starts. Can be
overridden by inheritance. Subject - controller. Refer to Registration sections on usage of this event.
* user.registered - on successfully completed registration. Subject is controller.
* user.activated - on user successfully activation. Needs activation to be turned on.

Basically 2 last events are used to send emails.

Alternative to events (new in 0.9.5)
------------------------------------

To extend user actions you don't have to assign listeners to events. You can also override this dummy methods in your controller.

* preSignin - executed after the Signin form is created but before it was binded. Access for with $this->form
* postSignin - executed when the user signs in. You can use this method for redirection.
* preRegister - executed when Registration for is created but not binded. Use $this->form to access the form.
* postRegister - executed after the new user is created, but not activated.
* preActivate - executed before the Activate action runs
* postActivate - executed when user is activated but not signed in

Tasks (new in 0.9.5)
-------

Added several tasks for user management

* user:create username password - creates user
* user:promote username - promotes a user to superadmin
* user:ban - deactivates (bans) user

I18n
----
All the messages and templates are I18n-ready. Please check doAuthMailer class to add translations to email subjects and doAuthActions to translate flash messages

Contribute
----------
You can always fork this project on Github.
http://github.com/DavertMik/doAuthPlugin
Bugfixes, enhancements, bugreports are always welcome.

TODO
----
* test everything, cover with functional tests