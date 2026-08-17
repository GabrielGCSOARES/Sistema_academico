import React, { useState } from 'react';
import DirectorHome from './components/DirectorHome/DirectorHome';
import StudentHome from './components/StudentHome/StudentHome';
import './App.css';

function App() {
  const [view, setView] = useState('director');

  return (
    <div className="App">
      {view === 'director' && (
        <DirectorHome onNavigateToStudent={() => setView('student')} />
      )}
      {view === 'student' && (
        <StudentHome onBack={() => setView('director')} />
      )}
    </div>
  );
}

export default App;