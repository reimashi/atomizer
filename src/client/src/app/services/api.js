import sha1 from 'sha1';

const TOKEN = "token_id";

/**
 * Api client
 */
class ApiService {
    constructor($rootScope, $http, jwtHelper, authManager) {
        this.baseurl = window.location.protocol + "//api." + window.location.hostname + "/index.php";
        this.$rootScope = $rootScope;
        this.$http = $http;
        this.jwtHelper = jwtHelper;
        this.authManager = authManager;
    }

    /**
     * Create a new user in the app
     * @param username User name
     * @param password Plain password
     * @returns {Promise}
     */
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

    /**
     * Login a user with username and password
     * @param username User name
     * @param password Plain password
     * @returns {Promise}
     */
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

    /**
     * Internal login
     * @param token
     * @private
     */
    _login(token) {
        sessionStorage.setItem(TOKEN, String(token));
        this.authManager.authenticate();
    }

    /**
     * Logout current user in the app
     */
    logout() {
        sessionStorage.removeItem(TOKEN);
        this.authManager.unauthenticate();
    }

    /**
     * Check if current user is logued in
     * @returns {boolean}
     */
    isLoguedin() {
        let now = new Date();
        let token = this.token();

        if (token !== null) {
            let date = this.jwtHelper.getTokenExpirationDate(this.token());
            return (date && date.getTime() > now.getTime());
        }
        else return false;
    }

    /**
     * Get JWT token
     */
    token() {
        return sessionStorage.getItem(TOKEN);
    }

    /**
     * Get feeds for the current user
     * @param update True to update feeds in server
     * @returns {Promise}
     */
    getFeeds(update) {
        if (update) update = 1; else update = 0;

        return new Promise((accept, reject) => {
            return this.$http.get(this.baseurl + "/feeds", { params: { update: update } })
                .then((response) => {
                    if (response.status === 200 || response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch((err) => reject(err));
        });
    }

    /**
     * Add new feed in server
     * @param url Url of the feed
     * @returns {Promise}
     */
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

    /**
     * Delete the feed with id for the current user
     * @param id Feed id
     */
    delFeed(id) {
        throw new Error("Not implemented");
    }

    /**
     * Tag the feed item with [itemId] as readed.
     * @param feedId Feed id
     * @param itemId Item/article id
     * @returns {Promise}
     */
    tagFeedReaded(feedId, itemId) {
        return new Promise((accept, reject) => {
            return this.$http.put(this.baseurl + "/feeds/tag", {
                feed_id: Number(feedId),
                item_id: Number(itemId),
                readed: true
            })
                .then((response) => {
                    if (response.status === 200 || response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch((err) => reject(err));
        });
    }

    /**
     * Tag the feed item with [itemId] to read later.
     * @param feedId Feed id
     * @param itemId Item/article id
     * @returns {Promise}
     */
    tagFeedReadLater(feedId, itemId) {
        return new Promise((accept, reject) => {
            return this.$http.put(this.baseurl + "/feeds/tag/", {
                feed_id: Number(feedId),
                item_id: Number(itemId),
                read_later: true
            })
                .then((response) => {
                    if (response.status === 200 || response.status === 201) accept(response.data);
                    else reject(response.data);
                })
                .catch((err) => reject(err));
        });
    }
}

export { ApiService };