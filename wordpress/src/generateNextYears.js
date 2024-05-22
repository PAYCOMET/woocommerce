    //Array of years for expiration date
    export const generateNextYears = () => {
        const firstYear = new Date().getFullYear();
        const lastYears = 15; 
        let years = [];
      
        for (let i = 0; i < lastYears; i++) {
          const year = firstYear + i;
          years.push(year);
        }
    
        return years;
    };