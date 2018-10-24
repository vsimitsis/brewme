<?php

namespace BrewMe\Controller;

use BrewMe\CFG;
use BrewMe\DBI\UserDBI;
use BrewMe\Model\Order;
use BrewMe\Model\User;

class OrderController extends BaseController {

    /**
     * The available commands
     */
    const COMMANDS = [
        'prepare',
        'grab',
        'set',
        'done',
        'cancel',
        'help',
    ];

    /**
     * The available brews
     */
    const DRINKS = [
        'coffee',
        'tea'
    ];

    /**
     * The user arguments
     * @var array
     */
    private $args;

    /**
     * The command user typed
     * @var null
     */
    private $command = null;

    /**
     * The comments user typed
     * @var null
     */
    private $comments = null;

    /**
     * The type of the drink user selected
     * @var null
     */
    private $type = null;

    /**
     * Handle the post request
     *
     * @return bool|false|string
     */
    public function post()
    {
        //Validate the post and grab the arguments
        if ($this->validateInput()) {
            return $this->validateInput();
        }

        switch ($this->args[0]) {
            case 'prepare':
                return $this->storeOrder();
                break;
            case 'grab':
                return $this->storeOrder();
                break;
            case 'set':
                return $this->setOrder();
                break;
            case 'done':
                return $this->done();
            case 'help':
                return $this->getHelp();
                break;
            default:
                return $this->getHelp();
        }
    }

    private function storeOrder()
    {
        $key = CFG::get("SLACK_OAUTH_ACCESS_TOKEN");
        //Fetch the user
        $username = $_POST['user_name'];

        $user = UserDBI::findUserByUsername($username);

        if ($user) {
            $user = new User($user);
        } else {
            // Create new user
            $user = UserDBI::createUser([
                'username' => $username
            ]);
        }

        $order = new Order([
            'user_id' => $user->id,
            'type' => $this->type,
            'comments' => $this->comments,
            'status' => Order::STATUS_PENDING
        ]);

        return json_encode([$this->command , $this->type , $this->comments]);
    }

    private function setOrder()
    {
        return "Setting Order";
    }

    private function done()
    {
        return 'Done all orders';
    }

    /**
     * Validate the user's input and store the args
     */
    private function validateInput()
    {
        //Grab the post data
        $post = htmlspecialchars($_POST['text'], ENT_QUOTES);

        //Seperate arguments and comments and make run some validations
        $parts          = explode(":", $post);
        $this->args     = explode(" ", $parts[0]);
        $this->command  = $this->args[0];
        $this->type     = $this->args[1];
        $this->comments = $parts[1];

        if (empty($this->command)) {
            $msg = 'Welcome to BrewMe. Type `/brew help` for the full list of all valid commands';
            return $this->respond($msg);
        }
        else if (!in_array($this->command, self::COMMANDS)) {
            $msg = '`' . $this->command . '` is not a valid command. Type `brew help` for the full list of valid commands';
            return $this->respond($msg);
        }

        if ($this->command === 'prepare') {
            if (empty($this->type)) {
                $msg = 'You must declare the type of the drink. Type `/brew help` for the full list of all valid commands';
                return $this->respond($msg);
            }
            else if (!in_array($this->type, self::DRINKS)) {
                $msg = 'Sorry but we don\'t currently serve `' . $this->type . '`. Type `brew help` for the full list of valid commands and drinks.';
                return $this->respond($msg);
            }
        }
        return false;
    }

    /**
     * Returns the help command list
     *
     * @return false|string
     */
    private function getHelp()
    {
        $msg = "
        We need to write the help part
        ";
        return $this->respond($msg);
    }

    /**
     * Returns a respond
     *
     * @param string $msg
     * @return false|string
     */
    private function respond($msg = "There was a problem. Please try again or type `/brew help`")
    {
        return json_encode([
            'response_type' => "in_channel",
            "text" => "Brew ordering system powered by Buildempire",
            "attachments" => [
                [
                    "text" => $msg
                ]
            ]
        ]);
    }
}