import logo from './logo.svg';
import './App.css';
import { HashRouter, Route } from 'react-router-dom';

function App() {
  return (
    <HashRouter>
      <Route path='/' exact />
      <Route path='/signup' />
      <Route path='/login' />
      <Route path='/verifyEmail' />
    </HashRouter>
  );
}

export default App;
