package com.networkmanagerapp;

import java.io.BufferedReader;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.MalformedURLException;

import org.apache.http.HttpHost;
import org.apache.http.HttpResponse;
import org.apache.http.HttpVersion;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;
import org.apache.http.params.HttpProtocolParams;

import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.preference.PreferenceManager;
import android.util.Log;

/**
 * This class creates an IntentService to download the XML files in a service in the background
 * @author rjs07u
 *
 */
public class XMLBackgroundDownloaderService extends IntentService {
	public static final String NEW_DATA_AVAILABLE = "New XML Downloaded";
	private NotificationManager mNM;
	
	/**
	 * The constructor. Passes a name to the superclass for debugging purposes.
	 */
	public XMLBackgroundDownloaderService() {
		super("XMLBackgroundDownloaderService");
	}
	
	/**
	 * Displays a notification in the system notification area
	 */
	private void showNotification(){
		CharSequence text = getText(R.string.download_service_started);
		Notification notification = new Notification(R.drawable.ic_stat_networkman, text, System.currentTimeMillis());
		PendingIntent contentIntent = PendingIntent.getActivity(this, 0, new Intent(this, NetworkManagerMainActivity.class), 0);
		notification.setLatestEventInfo(this, text, text, contentIntent);
		mNM.notify(R.string.download_service_started, notification);
	}

	/**
	 * Delegate method to run the specified intent in another thread.
	 * @param arg0 The intent to run in the background
	 */
	@Override
	protected void onHandleIntent(Intent arg0) {
		mNM = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
		showNotification();
		String filename = arg0.getStringExtra("FILENAME");
		String xmlFile = arg0.getStringExtra("XMLFILE");
		try{
			String password = PreferenceManager.getDefaultSharedPreferences(this).getString("password_preference", "");
			String scriptUrl = "http://" + PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1") + ":1080" +filename;
			HttpParams params = new BasicHttpParams();
	        HttpProtocolParams.setVersion(params, HttpVersion.HTTP_1_1);
	        HttpProtocolParams.setContentCharset(params, "utf-8");
	        
	     // Set the timeout in milliseconds until a connection is established.
			int timeoutConnection = 3000;
			HttpConnectionParams.setConnectionTimeout(params, timeoutConnection);
			// Set the default socket timeout (SO_TIMEOUT) 
			// in milliseconds which is the timeout for waiting for data.
			int timeoutSocket = 20000;
			HttpConnectionParams.setSoTimeout(params, timeoutSocket);
			HttpHost targetHost = new HttpHost(PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1"), 1080, "http"); 
			DefaultHttpClient client = new DefaultHttpClient(params);
			client.getCredentialsProvider().setCredentials(
			        new AuthScope(targetHost.getHostName(), targetHost.getPort()), 
			        new UsernamePasswordCredentials("root", password));
			HttpGet request = new HttpGet(scriptUrl);
			
			
			HttpResponse response = client.execute(request);
			Log.d("XBDS", response.getStatusLine().toString());
			InputStream in = response.getEntity().getContent();
            BufferedReader reader = new BufferedReader(new InputStreamReader(in));
            StringBuilder str = new StringBuilder();
            String line = null;
            while((line = reader.readLine()) != null){
                str.append(line + "\n");
            }
            in.close();
            
			if(str.toString().equals("Success\n")){
				String xmlUrl = "http://" + PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1") + ":1080/xml" +xmlFile;
				request = new HttpGet(xmlUrl);
				HttpResponse xmlData = client.execute(request);
				in = xmlData.getEntity().getContent();
				reader = new BufferedReader(new InputStreamReader(in));
				str = new StringBuilder();
				line = null;
				while((line = reader.readLine()) != null){
	                str.append(line + "\n");
	            }
	            in.close();
				
				FileOutputStream fos = openFileOutput(xmlFile.substring(1), Context.MODE_PRIVATE);
				fos.write(str.toString().getBytes());
				fos.close();
			}
		}catch(MalformedURLException ex){
			Log.e("NETWORKMANAGER_XBD_MUE", ex.getMessage());
		} catch (IOException e) {
			try{
				Log.e("NETWORK_MANAGER_XBD_IOE", e.getMessage());
			} catch(NullPointerException ex){
				Log.e("Network_manager_xbd_npe", ex.getLocalizedMessage());
			}
			
		}finally{
			mNM.cancel(R.string.download_service_started);
			Intent bci = new Intent(NEW_DATA_AVAILABLE);
			sendBroadcast(bci);
			stopSelf();
		}
	}
}