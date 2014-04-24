import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;

import java.net.URL;
import java.net.URLEncoder;
import java.net.MalformedURLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.json.JSONObject;
import org.json.XML;


public class SearchServlet extends HttpServlet {


    public void doGet(HttpServletRequest request,
                      HttpServletResponse response)
        throws IOException, ServletException
    {		
		response.setContentType("application/json; charset=utf-8");
		PrintWriter out = response.getWriter();
		try
		{				
			String linkStockInfo = "http://default-environment-ssmip4ghfp.elasticbeanstalk.com/?symbol=";
			String company = request.getParameter("symbol");		
			String errorXmlContent = "<result>Symbol not found</result>";
			StringBuffer xmlContent = new StringBuffer();
			if (company == null || company.trim().length() == 0)
			{
				xmlContent.append(errorXmlContent);
			}
			else
			{
				URL urlStockInfo = new URL(linkStockInfo + company);
				BufferedReader stockInfo = new BufferedReader(new InputStreamReader(urlStockInfo.openStream(), "UTF8"));
				String stockLine = "";
				while ((stockLine = stockInfo.readLine()) != null)
					xmlContent.append(stockLine);
				stockInfo.close();
			}
			JSONObject jsonObject = XML.toJSONObject(xmlContent.toString());
			out.println(jsonObject);
		}
		catch (MalformedURLException urlex)
		{
			String errorXmlContent = "<result>Malformed URL</result>";
			JSONObject jsonObject = XML.toJSONObject(errorXmlContent.toString());
			out.println(jsonObject);
		}
		catch (IOException ioex)
		{
			String errorXmlContent = "<result>IO Exception</result>";
			JSONObject jsonObject = XML.toJSONObject(errorXmlContent.toString());
			out.println(jsonObject);
		}
		catch (Exception e)
		{
			String errorXmlContent = "<result>Exception encountered:" + e.getMessage() + "</result>";
			JSONObject jsonObject = XML.toJSONObject(errorXmlContent.toString());
			out.println(jsonObject);
		}
    }
}



