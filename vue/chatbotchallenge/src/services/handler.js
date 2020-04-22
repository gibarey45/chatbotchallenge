import httpClient from "../helpers/HttpClient";
export default {
  login(data) {
    return httpClient.post("/auth/token", data).then(res=>res.data);
  },
  register(data) {
    return httpClient.post('/auth/register', data).then(res=>res.data);
  },
  balance(queryParams) {
    return httpClient.get('/balance', queryParams).then(res=>res.data);
  },
  exchange(queryParams) {
    return httpClient.get('/exchange', queryParams).then(res=>res.data);
  },
  changeCurrency(data) {
    return httpClient.post('/currency/change', data).then(res=>res.data);
  },
  showCurrency() {
    return httpClient.get('/currency/get').then(res=>res.data);
  },
  deposit(data) {
    return httpClient.post('/deposit', data).then(res=>res.data);
  },
  withdraw(data) {
    return httpClient.post('/withdraw', data).then(res=>res.data);
  }
};
