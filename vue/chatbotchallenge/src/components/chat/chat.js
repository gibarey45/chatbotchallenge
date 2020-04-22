import service from '../../services/handler';

export default {
    name: 'chat',
    data: () => ({
        message: '',
        messages: [],
        token: '',
        name: '',
        username: '',
        password: '',
        passwordConfirmation: '',
        email: '',
        account: '',
        balance: '',
        commands: [
            'help',
            'register',
            'login',
            '/exchange',
            '/balance',
            '/currency',
            '/deposit',
            '/withdraw',
            'logout'
        ]
    }),
    mounted() {
        this.showWelcome();
        this.showFirstInstructions();
    },
    methods: {
        sendMessage(message, author) {
            if (message === '') {
                return;
            }
            this.messages.push({
                text: message,
                author: author
            });
            if (
                this.commands.indexOf(message.toLowerCase().split(" ")[0]) === -1 &&
                author === 'client'
            ) {
                this.messages.push({
                    text: 'Invalid command',
                    author: 'server'
                });
            }
            if (message.toLowerCase().startsWith('help')) {
                this.showHelp()
            }
            if (message.toLowerCase().startsWith('register')) {
                this.$modal.show('register')
            }
            if (message.toLowerCase().startsWith('login')) {
                if (!this.userLoggedIn()) {
                    this.$modal.show('authentication');
                }
                else{
                    this.sendMessage('User previously logged', 'server')
                }
            }
            if (message.toLowerCase().startsWith('/exchange')) {
                this.actionExchange(message)
            }
            if (message.toLowerCase().startsWith('/balance')) {
                this.actionBalance(message)
            }
            if (message.toLowerCase().startsWith('/currency')) {
                this.actionCurrency(message)
            }
            if (message.toLowerCase().startsWith('/deposit')) {
                this.actionDeposit(message)
            }
            if (message.toLowerCase().startsWith('/withdraw')) {
                this.actionWithdraw(message)
            }
            if (message.toLowerCase().startsWith('logout')) {
                localStorage.token = ''
                this.sendMessage('User logged out', 'server')
            }
            if (message.toLowerCase().startsWith('clear')) {
                this.clearAllMessages()
            }
            this.message = ''
        },
        showWelcome() {
            this.sendMessage(
                'Welcome to ChatBot, Please type "help" for commands',
                'server'
            );
        },
        showFirstInstructions() {
            let message =
                "You must be registered first<br>" +
                "For register you should write the command:<br>" +
                '"register"<br>' +
                "If you are already registered to login you should write the command:<br>" +
                '"login"';
            this.sendMessage(message, 'server');
        },
        showLoginHelp() {
            let loginHelp = 'You must register and log in to use chatbot operations<br>' +
                'For register you can use the command "register" and for log in "login"'
            this.sendMessage(loginHelp, 'server')
        },
        userLoggedIn() {
            if (localStorage.token !== '') {
                return true
            }
            return false
        },
        showHelp() {
            if (!this.userLoggedIn()) {
                this.showLoginHelp();
                return
            }
            let help = 'To obtain the balance, use "/balance + the currency code" in which you want to obtain it<br>' +
                'Ex. /balance USD<br>' +
                'To obtain actual currency use "/currency" and to change it "/currency + the currency code"<br>' +
                'To make a deposit use "/deposit + amount + currency code" Ex. /deposit 10 EUR<br>' +
                'To make a withdraw use "/withdraw + amount + currency code " Ex. /withdraw 10 USD<br>' +
                'To make money exchanges use "/exchange + currency-code-from currency-code-to + amount"<br>' +
                'Ex. /exchange USD EUR 10<br>' +
                'Thank You and Good Luk'

            this.sendMessage(help, 'server')
        },
        actionRegister() {
            if (this.password === this.passwordConfirmation) {
                service
                    .register({
                        "name": this.name, "username": this.username, "email": this.email, "password": this.password
                    })
                    .then((response) => {
                        this.$modal.hide('register');
                        this.cleanRegister();
                        this.sendMessage('Welcome ' + response.result + ', Login first to be able to work in the chatbot', 'server')
                    })
                    .catch((error) => {
                        if (error.response.status === 412) {
                            this.$modal.hide('register');
                            this.sendMessage('Sorry, User already exists', 'server');
                        }
                    })
                return
            }
            else{
                this.$modal.hide('register');
                this.sendMessage('Password confirmation is wrong', 'server')
            }
        },
        actionLogin() {
            service
                .login({"username": this.username, "password": this.password})
                .then(response => {
                    localStorage.token = response.token;
                    this.sendMessage('User logged in successfully', 'server');
                })
                .catch(error => {
                    if (error.response.data.code === 401) {
                        this.sendMessage(error.response.data.message, 'server');
                    }
                });
            this.$modal.hide('authentication');
            this.cleanLogin();
        },
        actionExchange(message) {
            let dataArray = message.split(' ');
            if (dataArray.length === 4) {
                service
                    .exchange({
                    params: {
                        currencyFrom: dataArray[1],
                        currencyTo: dataArray[2],
                        amount: dataArray[3]
                    }
                })
                    .then((response) => {
                        this.sendMessage('Exchange result: ' + response.result.toFixed(2) + ' ' + dataArray[2].toUpperCase(), 'server')
                    })
                    .catch((error) => {
                        if (error.response.status === 409) {
                            this.sendMessage('Invalid exchange data', 'server')
                            return
                        }
                        if (error.response.status === 401) {
                            this.sendMessage('Session expired, please, log in again', 'server')
                            return
                        }
                        this.sendMessage('Unknown Error', 'server')
                    })
            }
            else{
                this.sendMessage('Parameters error', 'server')
            }
        },
        actionBalance(message) {
            let dataArray = message.split(' ');
            service
                .balance({
                    params: {
                        currency: dataArray[1]
                    }
                })
                .then((response) => {
                        this.sendMessage('Your balance is ' + response.result.toFixed(2) + ' ' + dataArray[1].toUpperCase(), 'server');
                    }
                )
                .catch((error) => {
                    if (error.response.status === 409) {
                        this.sendMessage('Invalid balance data request', 'server');
                        return
                    }
                    if (error.response.status === 401) {
                        this.sendMessage('Session expired, please, log in again', 'server');
                        return
                    }
                    this.sendMessage('Unknown Error', 'server')
                })
        },
        actionCurrency(message) {
            let dataArray = message.split(' ');
            if (dataArray.length === 1) {
                service
                    .showCurrency()
                    .then((response) => {
                        this.sendMessage('Actual default currency: ' + response.result, 'server')
                    })
                    .catch((error) => {
                        if (error.response.status === 401) {
                            this.sendMessage('Session expired, please, log in again', 'server')
                            return
                        }
                        this.sendMessage('Unknown Error', 'server')
                    })
                return
            }
            service
                .changeCurrency({
                    'currency': dataArray[1]
                })
                .then((response) => {
                    this.sendMessage('New default currency: ' + response.result, 'server')
                })
                .catch((error) => {
                    if (error.response.status === 409) {
                        this.sendMessage('Invalid currency', 'server')
                        return
                    }
                    if (error.response.status === 401) {
                        this.sendMessage('Session expired, please, log in again', 'server')
                        return
                    }
                    this.sendMessage('Unknown Error', 'server')
                })
        },
        actionDeposit(message) {
            let dataArray = message.split(' ');
            service.deposit(
                {
                    'amount': dataArray[1],
                    'currency': dataArray[2]
                })
                .then((response) => {
                        this.sendMessage('Your new balance is: ' + response.balance.toFixed(2) + ' ' + response.currency, 'server');
                    }
                )
                .catch((error) => {
                    if (error.response.status === 409) {
                        this.sendMessage('Invalid deposit data', 'server')
                        return
                    }
                    if (error.response.status === 401) {
                        this.sendMessage('Session expired, please, log in again', 'server')
                        return
                    }
                    this.sendMessage('Unknown Error', 'server')
                })
        },
        actionWithdraw(message) {
            let dataArray = message.split(' ');
            service
                .withdraw(
                    {
                        'amount': dataArray[1],
                        'currency': dataArray[2]
                    })
                .then((response) => {
                        this.sendMessage('Your new balance is: ' + response.balance.toFixed(2) + ' ' + response.currency, 'server');
                    }
                )
                .catch((error) => {
                    if (error.response.status === 409) {
                        this.sendMessage('Invalid withdraw data', 'server')
                        return
                    }
                    if (error.response.status === 401) {
                        this.sendMessage('Session expired, please, log in again', 'server')
                        return
                    }
                    this.sendMessage('Unknown Error', 'server')
                })
        },
        cleanLogin() {
            this.username = '';
            this.password = '';
        },
        cleanRegister() {
            this.name= '';
            this.username= '';
            this.email = '';
            this.password = '';
            this.passwordConfirmation = '';
        },
        clearAllMessages() {
            this.messages = []
        },
    }
};
