import sha1 from 'sha1';

const TOKEN = "token_id";

class ApiService {
    constructor($rootScope, $http, jwtHelper, authManager) {
        this.baseurl = window.location.protocol + "//api." + window.location.hostname + "/index.php";
        this.$rootScope = $rootScope;
        this.$http = $http;
        this.jwtHelper = jwtHelper;
        this.authManager = authManager;
    }

    // Create a new user in the app
    userAdd(username, password) {
        return new Promise((accept, reject) => {
            return this.$http.post(this.baseurl + "/users", {
                username: String(username).trim(),
                password: sha1(String(password))
            }, {
                skipAuthorization: true
            })
                .then((response) => {
                console.log(response.data);
                    // Login in frontend
                    this._login(response.data.token);
                    if (response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch(reject);
        })
    }

    login(username, password) {
        return new Promise((accept, reject) => {
            return this.$http.post(this.baseurl + "/users/token", {
                username: String(username).trim(),
                password: sha1(String(password))
            }, {
                skipAuthorization: true
            })
                .then((response) => {
                    this._login(response.data.token);
                    if (response.status === 200 || response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch((err) => reject(err));
        })
    }

    _login(token) {
        sessionStorage.setItem(TOKEN, String(token));
        this.authManager.authenticate();
    }

    logout() {
        sessionStorage.removeItem(TOKEN);
        this.authManager.unauthenticate();
    }

    isLoguedin() {
        let now = new Date();
        let token = this.token();

        if (token !== null) {
            let date = this.jwtHelper.getTokenExpirationDate(this.token());
            return (date && date.getTime() > now.getTime());
        }
        else return false;
    }

    token() {
        return sessionStorage.getItem(TOKEN);
    }

    // Get feeds for the current user
    getFeeds(limit, step) {
        return [{
            id: 1,
            title: "test title"
        }];
    }

    // Add a feed for the current user
    addFeed(url) {
        return new Promise((accept, reject) => {
            return this.$http.post(this.baseurl + "/feeds", {
                url: String(url).trim()
            })
                .then((response) => {
                    if (response.status === 200 || response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch((err) => reject(err));
        });
    }

    // Delete the feed with id for the current user
    delFeed(id) {

    }

    // Tag the feed item with [id] as readed.
    tagFeedReaded(id) {

    }

    // Tag the feed item with [id] to read later.
    tagFeedReadLater(id) {

    }
}

export { ApiService };