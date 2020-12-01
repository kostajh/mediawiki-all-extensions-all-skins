local datatable2 = {}
local php

--[[
@brief Setup the interface.
--]]

function datatable2.setupInterface( options )
   -- Remove setup function.
   datatable2.setupInterface = nil
   
   -- Copy the PHP callbacks to a local variable, and remove the global one.
   php = mw_interface
   mw_interface = nil
   
   -- Do any other setup here.
   datatable2.select = php.select

   -- Install into the mw global.
   mw = mw or {}
   mw.ext = mw.ext or {}
   mw.ext.datatable2 = datatable2
   
   -- Indicate that we're loaded.
   package.loaded['mw.ext.datatable2'] = datatable2
end

return datatable2
